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
 * @psalm-import-type ESCompletionSuggesters from ClientInterface
 */
final class InstantSearchController extends AbstractController implements InstantSearchControllerInterface
{
    /**
     * @param int<1,10_000> $maxResults
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly ChannelContextInterface $channelContext,
        private readonly IndexNameGeneratorInterface $indexNameGenerator,
        private readonly DocumentTypeProviderInterface $documentTypeProvider,
        private readonly QueryBuilderInterface $queryBuilder,
        private readonly QueryResultMapperInterface $queryResultMapper,
        private readonly int $maxResults,
        private readonly int $completionSuggestersSize,
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

        $completionSuggesters = $this->client->completionSuggesters(
            $this->queryBuilder->buildCompletionSuggestersQuery($query, 'suggest', $this->completionSuggestersSize),
            $indexAliasNames,
        );

        $esResult = $this->client->query(
            $this->queryBuilder->buildSearchQuery(
                $query,
                null,
                $this->maxResults,
                null,
                false,
                null,
                1,
            ),
            $indexAliasNames,
        );
        $queryResult = $this->queryResultMapper->map($esResult);

        return $this->render('@WebgriffeSyliusElasticsearchPlugin/InstantSearch/results.html.twig', [
            'query' => $query,
            'queryResult' => $queryResult,
            'completionSuggesters' => $this->buildCompletionSuggesters($completionSuggesters),
        ]);
    }

    /**
     * @param ESCompletionSuggesters $completionSuggesters
     */
    private function buildCompletionSuggesters(array $completionSuggesters): array
    {
        $suggestions = [];
        foreach ($completionSuggesters as $suggestion) {
            $suggestionData = reset($suggestion);
            if ($suggestionData === false) {
                continue;
            }
            $options = $suggestionData['options'];
            if (count($options) === 0) {
                continue;
            }
            foreach ($options as $option) {
                $suggestions[] = $option['text'];
            }
        }

        return $suggestions;
    }
}
