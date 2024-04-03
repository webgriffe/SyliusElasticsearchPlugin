<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Controller;

use Pagerfanta\Pagerfanta;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webgriffe\SyliusElasticsearchPlugin\Builder\QueryBuilderInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\FilterHelper;
use Webgriffe\SyliusElasticsearchPlugin\Form\Type\SearchType;
use Webgriffe\SyliusElasticsearchPlugin\Generator\IndexNameGeneratorInterface;
use Webgriffe\SyliusElasticsearchPlugin\Helper\SortHelperInterface;
use Webgriffe\SyliusElasticsearchPlugin\Mapper\QueryResultMapperInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\ResponseInterface;
use Webgriffe\SyliusElasticsearchPlugin\Pagerfanta\ElasticsearchSearchQueryAdapter;
use Webgriffe\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;
use Webmozart\Assert\Assert;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @psalm-import-type ESSuggestOption from ClientInterface
 * @psalm-import-type ESSuggests from ClientInterface
 */
final class SearchController extends AbstractController implements SearchControllerInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly ChannelContextInterface $channelContext,
        private readonly IndexNameGeneratorInterface $indexNameGenerator,
        private readonly DocumentTypeProviderInterface $documentTypeProvider,
        private readonly FormFactoryInterface $formFactory,
        private readonly QueryBuilderInterface $queryBuilder,
        private readonly QueryResultMapperInterface $queryResultMapper,
        private readonly SortHelperInterface $sortHelper,
        private readonly int $searchDefaultPageLimit,
    ) {
    }

    public function __invoke(Request $request, ?string $query = null): Response
    {
        $form = $this->formFactory->create(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $query */
            $query = $form->get('query')->getData();

            return $this->redirectToRoute('sylius_shop_search', ['query' => $query]);
        }
        if ($query === null) {
            throw $this->createNotFoundException();
        }
        $channel = $this->channelContext->getChannel();
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $indexAliasNames = [];
        foreach ($this->documentTypeProvider->getDocumentsType() as $documentType) {
            $indexAliasNames[] = $this->indexNameGenerator->generateAlias(
                $channel,
                $documentType,
            );
        }

        /** @var array<string, string> $sorting */
        $sorting = $request->query->all('sorting');
        $sorting = $this->sortHelper->retrieveSearchSorting($sorting);
        $size = $request->query->getInt('limit', $this->searchDefaultPageLimit);
        $page = $request->query->getInt('page', 1);

        /** @var array<string, array<string, string>> $requestFilters */
        $requestFilters = $request->query->all('filters');
        $filters = FilterHelper::retrieveFilters($requestFilters);

        $esSearchQueryAdapter = new ElasticsearchSearchQueryAdapter(
            $this->queryBuilder,
            $this->client,
            $this->queryResultMapper,
            $indexAliasNames,
            $sorting,
            $filters,
            $query,
        );
        /**
         * @psalm-suppress InvalidArgument Why Psalm??
         */
        $paginator = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $esSearchQueryAdapter,
            $page,
            $size,
        );
        // This prevents Pagerfanta from querying ES from a template
        /** @var ResponseInterface[] $results */
        $results = $paginator->getCurrentPageResults();
        if (count($results) === 1) {
            $result = $results[0];

            return $this->redirectToRoute($result->getRouteName(), $result->getRouteParams());
        }
        $suggesters = $this->client->suggesters(
            $this->queryBuilder->buildSuggestersQuery($query),
            $indexAliasNames,
        );

        return $this->render('@WebgriffeSyliusElasticsearchPlugin/Search/results.html.twig', [
            'query' => $query,
            'paginator' => $paginator,
            'filters' => $esSearchQueryAdapter->getQueryResult()->getFilters(),
            'queryResult' => $esSearchQueryAdapter->getQueryResult(),
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