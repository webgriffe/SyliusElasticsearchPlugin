<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Controller;

use Pagerfanta\Pagerfanta;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webgriffe\SyliusElasticsearchPlugin\Builder\QueryBuilderInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\DocumentType\ProductDocumentType;
use Webgriffe\SyliusElasticsearchPlugin\Form\SearchType;
use Webgriffe\SyliusElasticsearchPlugin\Generator\IndexNameGeneratorInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResult;
use Webgriffe\SyliusElasticsearchPlugin\Pagerfanta\ElasticsearchAdapter;
use Webgriffe\SyliusElasticsearchPlugin\Parser\DocumentParserInterface;
use Webgriffe\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;
use Webmozart\Assert\Assert;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ElasticsearchController extends AbstractController
{
    public function __construct(
        private readonly TaxonRepositoryInterface $taxonRepository,
        private readonly LocaleContextInterface $localeContext,
        private readonly ClientInterface $indexManager,
        private readonly ChannelContextInterface $channelContext,
        private readonly IndexNameGeneratorInterface $indexNameGenerator,
        private readonly DocumentTypeProviderInterface $documentTypeProvider,
        private readonly DocumentParserInterface $documentParser,
        private readonly FormFactoryInterface $formFactory,
        private readonly QueryBuilderInterface $queryBuilder,
    ) {
    }

    public function searchAction(Request $request, ?string $query = null): Response
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

        $indexes = [];
        foreach ($this->documentTypeProvider->getDocumentsType() as $documentType) {
            $aliasName = $this->indexNameGenerator->generateAlias($channel, $documentType);
            $indexes[] = $aliasName;
        }

        $esQuery = [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'match' => [
                                'name' => $query,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $result = $this->indexManager->query($esQuery, $indexes);
        $responses = [];
        /** @var array{_index: string, _id: string, score: float, _source: array} $hit */
        foreach ($result['hits']['hits'] as $hit) {
            $responses[] = $this->documentParser->parse($hit);
        }
        $queryResult = new QueryResult($responses);
        $results = new Pagerfanta(new ElasticsearchAdapter($queryResult));

        return $this->render('@WebgriffeSyliusElasticsearchPlugin/Search/results.html.twig', [
            'query' => $query,
            'results' => $results,
        ]);
    }

    public function taxonAction(Request $request, string $slug): Response
    {
        $localeCode = $this->localeContext->getLocaleCode();
        $taxon = $this->taxonRepository->findOneBySlug($slug, $localeCode);
        if (!$taxon instanceof TaxonInterface) {
            throw $this->createNotFoundException();
        }
        $channel = $this->channelContext->getChannel();
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $productIndexAliasName = $this->indexNameGenerator->generateAlias($channel, $this->documentTypeProvider->getDocumentType(ProductDocumentType::CODE));

        /** @var array<string, string> $sorting */
        $sorting = $request->query->all('sorting');
        if ($sorting === []) {
            $sorting = ['position' => 'asc'];
        }

        $query = $this->queryBuilder->buildTaxonQuery($taxon, $sorting);
        $result = $this->indexManager->query($query, [$productIndexAliasName]);
        $responses = [];
        /** @var array{_index: string, _id: string, score: float, _source: array} $hit */
        foreach ($result['hits']['hits'] as $hit) {
            $responses[] = $this->documentParser->parse($hit);
        }
        $queryResult = new QueryResult($responses);
        $products = new Pagerfanta(new ElasticsearchAdapter($queryResult));

        return $this->render('@WebgriffeSyliusElasticsearchPlugin/Product/index.html.twig', [
            'taxon' => $taxon,
            'products' => $products,
        ]);
    }
}
