<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Controller;

use Pagerfanta\Pagerfanta;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Webgriffe\SyliusElasticsearchPlugin\Builder\QueryBuilderInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\DocumentType\ProductDocumentType;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductIndexEvent;
use Webgriffe\SyliusElasticsearchPlugin\FilterHelper;
use Webgriffe\SyliusElasticsearchPlugin\Generator\IndexNameGeneratorInterface;
use Webgriffe\SyliusElasticsearchPlugin\Helper\SortHelperInterface;
use Webgriffe\SyliusElasticsearchPlugin\Mapper\QueryResultMapperInterface;
use Webgriffe\SyliusElasticsearchPlugin\Pagerfanta\ElasticsearchTaxonQueryAdapter;
use Webgriffe\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;
use Webmozart\Assert\Assert;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @psalm-import-type ESSuggestOption from ClientInterface
 */
final class ProductController extends AbstractController implements ProductControllerInterface
{
    public function __construct(
        private readonly TaxonRepositoryInterface $taxonRepository,
        private readonly LocaleContextInterface $localeContext,
        private readonly ClientInterface $client,
        private readonly ChannelContextInterface $channelContext,
        private readonly IndexNameGeneratorInterface $indexNameGenerator,
        private readonly DocumentTypeProviderInterface $documentTypeProvider,
        private readonly QueryBuilderInterface $queryBuilder,
        private readonly QueryResultMapperInterface $queryResultMapper,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SortHelperInterface $sortHelper,
        private readonly int $taxonDefaultPageLimit,
    ) {
    }

    public function __invoke(Request $request, string $slug): Response
    {
        $localeCode = $this->localeContext->getLocaleCode();
        $taxon = $this->taxonRepository->findOneBySlug($slug, $localeCode);
        if (!$taxon instanceof TaxonInterface) {
            throw $this->createNotFoundException();
        }
        $channel = $this->channelContext->getChannel();
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $productIndexAliasName = $this->indexNameGenerator->generateAlias(
            $channel,
            $this->documentTypeProvider->getDocumentType(ProductDocumentType::CODE),
        );

        /** @var array<string, string> $sorting */
        $sorting = $request->query->all('sorting');
        $sorting = $this->sortHelper->retrieveTaxonSorting($sorting);
        $size = $request->query->getInt('limit', $this->taxonDefaultPageLimit);
        $page = $request->query->getInt('page', 1);

        /** @var array<string, array<array-key, array{code: string, value: string}>> $requestFilters */
        $requestFilters = $request->query->all('filters');
        $filters = FilterHelper::retrieveFilters($requestFilters);

        $esTaxonQueryAdapter = new ElasticsearchTaxonQueryAdapter(
            $this->queryBuilder,
            $this->client,
            $this->queryResultMapper,
            [$productIndexAliasName],
            $sorting,
            $filters,
            $taxon,
        );
        /**
         * @psalm-suppress InvalidArgument Why Psalm??
         *
         * @var Pagerfanta<ProductInterface> $paginator
         */
        $paginator = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $esTaxonQueryAdapter,
            $page,
            $size,
        );
        // This prevents Pagerfanta from querying ES from a template
        $paginator->getCurrentPageResults();

        $this->eventDispatcher->dispatch(new ProductIndexEvent($taxon, $paginator));

        return $this->render('@WebgriffeSyliusElasticsearchPlugin/Product/index.html.twig', [
            'taxon' => $taxon,
            'products' => $paginator,
            'filters' => $esTaxonQueryAdapter->getQueryResult()->getFilters(),
            'queryResult' => $esTaxonQueryAdapter->getQueryResult(),
        ]);
    }
}
