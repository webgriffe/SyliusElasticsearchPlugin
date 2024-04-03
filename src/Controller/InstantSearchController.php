<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Controller;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webgriffe\SyliusElasticsearchPlugin\Builder\QueryBuilderInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\Generator\IndexNameGeneratorInterface;
use Webgriffe\SyliusElasticsearchPlugin\Mapper\QueryResultMapperInterface;
use Webgriffe\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;
use Webmozart\Assert\Assert;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @psalm-import-type ESSuggestOption from ClientInterface
 * @psalm-import-type ESSuggests from ClientInterface
 */
final class InstantSearchController extends AbstractController implements InstantSearchControllerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly ChannelContextInterface $channelContext,
        private readonly IndexNameGeneratorInterface $indexNameGenerator,
        private readonly DocumentTypeProviderInterface $documentTypeProvider,
        private readonly QueryBuilderInterface $queryBuilder,
        private readonly QueryResultMapperInterface $queryResultMapper,
    ) {
    }

    public function __invoke(Request $request, string $query): Response
    {
        $channel = $this->channelContext->getChannel();
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $indexAliasNames = [];
        foreach ($this->documentTypeProvider->getDocumentsType() as $documentType) {
            $indexAliasNames[] = $this->indexNameGenerator->generateAlias(
                $channel,
                $documentType,
            );
        }

        $suggesters = $this->client->suggesters(
            $this->queryBuilder->buildSuggestersQuery($query),
            $indexAliasNames,
        );

        $esResult = $this->client->query(
            $this->queryBuilder->buildSearchQuery($query),
            $indexAliasNames,
        );
        $queryResult = $this->queryResultMapper->map($esResult);

        return $this->render('@WebgriffeSyliusElasticsearchPlugin/InstantSearch/results.html.twig', [
            'query' => $query,
            'queryResult' => $queryResult,
            'suggesters' => $this->buildSuggestions($suggesters),
        ]);
    }

    /**
     * @param ESSuggests $suggesters
     */
    private function buildSuggestions(array $suggesters): array
    {
        $suggestions = [];
        foreach ($suggesters as $field => $suggestion) {
            foreach ($suggestion as $suggestionData) {
                if (count($suggestionData['options']) === 0) {
                    $suggestions[$field][] = $suggestionData['text'];

                    continue;
                }
                foreach ($suggestionData['options'] as $option) {
                    $suggestions[$field][] = $option['text'];
                }
            }
        }

        foreach ($suggestions as $field => $suggestion) {
            $suggestions[$field] = implode(' ', $suggestion);
        }

        return $suggestions;
    }
}
