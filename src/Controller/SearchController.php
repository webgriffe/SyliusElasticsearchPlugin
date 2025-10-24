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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webgriffe\SyliusElasticsearchPlugin\Builder\QueryBuilderInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\Event\SearchEvent;
use Webgriffe\SyliusElasticsearchPlugin\Form\Type\SearchType;
use Webgriffe\SyliusElasticsearchPlugin\Generator\IndexNameGeneratorInterface;
use Webgriffe\SyliusElasticsearchPlugin\Helper\FilterHelper;
use Webgriffe\SyliusElasticsearchPlugin\Helper\SortHelperInterface;
use Webgriffe\SyliusElasticsearchPlugin\Mapper\QueryResultMapperInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\ResponseInterface;
use Webgriffe\SyliusElasticsearchPlugin\Pagerfanta\ElasticsearchSearchQueryAdapter;
use Webgriffe\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;
use Webgriffe\SyliusElasticsearchPlugin\Validator\RequestValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @psalm-import-type ESSuggestOption from ClientInterface
 * @psalm-import-type ESTermSuggesters from ClientInterface
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
        private readonly RequestValidatorInterface $requestValidator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TranslatorInterface $translator,
        private readonly int $searchDefaultPageLimit,
    ) {
    }

    public function __invoke(Request $request, ?string $query = null): Response
    {
        $this->requestValidator->validate($request);

        $form = $this->formFactory->create(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                // The result of a POST must always be a redirect to a GET, so redirect to the search page with errors
                $this->addFlash('error', $this->translator->trans('webgriffe_sylius_elasticsearch_plugin.ui.some_error_occurred_during_search'));

                return $this->redirectToRoute('sylius_shop_homepage');
            }
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

        /** @var array<string, array<array-key, array{code: string, value: string}>> $requestFilters */
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
        /**
         * This prevents Pagerfanta from querying ES from a template
         *
         * @var ResponseInterface[] $results
         */
        $results = $paginator->getCurrentPageResults();
        if (count($results) === 1) {
            $result = $results[0];

            return $this->redirectToRoute($result->getRouteName(), $result->getRouteParams());
        }
        $termSuggesters = $this->client->termSuggesters(
            $this->queryBuilder->buildTermSuggestersQuery($query),
            $indexAliasNames,
        );
        $this->eventDispatcher->dispatch(new SearchEvent($query, $paginator, $termSuggesters));

        return $this->render('@WebgriffeSyliusElasticsearchPlugin/Search/results.html.twig', [
            'query' => $query,
            'paginator' => $paginator,
            'filters' => $esSearchQueryAdapter->getQueryResult()->getFilters(),
            'queryResult' => $esSearchQueryAdapter->getQueryResult(),
            'termSuggesters' => $this->buildTermSuggesters($query, $termSuggesters),
        ]);
    }

    /**
     * @param ESTermSuggesters $termSuggesters
     */
    private function buildTermSuggesters(string $query, array $termSuggesters): array
    {
        $suggestions = [];
        foreach ($termSuggesters as $suggestion) {
            foreach ($suggestion as $suggestionData) {
                $options = $suggestionData['options'];
                if (count($options) === 0) {
                    continue;
                }
                $textToReplace = $suggestionData['text'];
                foreach ($options as $option) {
                    $replaceTerm = $option['text'];
                    $suggestionKey = str_replace($textToReplace, $replaceTerm, $query);
                    $suggestionHtml = str_replace($textToReplace, '<strong>' . $replaceTerm . '</strong>', $query);
                    $suggestions[$suggestionKey] = $suggestionHtml;
                }
            }
        }

        return $suggestions;
    }
}
