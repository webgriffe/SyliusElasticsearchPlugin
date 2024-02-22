<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Controller;

use LRuozzi9\SyliusElasticsearchPlugin\Client\ClientInterface;
use LRuozzi9\SyliusElasticsearchPlugin\DocumentType\ProductDocumentType;
use LRuozzi9\SyliusElasticsearchPlugin\Form\SearchType;
use LRuozzi9\SyliusElasticsearchPlugin\Generator\IndexNameGeneratorInterface;
use LRuozzi9\SyliusElasticsearchPlugin\Model\QueryResult;
use LRuozzi9\SyliusElasticsearchPlugin\Pagerfanta\ElasticsearchAdapter;
use LRuozzi9\SyliusElasticsearchPlugin\Parser\DocumentParserInterface;
use LRuozzi9\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;
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

        return $this->render('@LRuozzi9SyliusElasticsearchPlugin/Search/results.html.twig', [
            'query' => $query,
            'results' => $results,
        ]);
    }

    public function taxonAction(string $slug): Response
    {
        $localeCode = $this->localeContext->getLocaleCode();
        $taxon = $this->taxonRepository->findOneBySlug($slug, $localeCode);
        if (!$taxon instanceof TaxonInterface) {
            throw $this->createNotFoundException();
        }
        $channel = $this->channelContext->getChannel();
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $productIndexAliasName = $this->indexNameGenerator->generateAlias($channel, $this->documentTypeProvider->getDocumentType(ProductDocumentType::CODE));
        $query = [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'match' => [
                                'taxons.sylius-id' => $taxon->getId(),
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $result = $this->indexManager->query($query, [$productIndexAliasName]);
        $responses = [];
        /** @var array{_index: string, _id: string, score: float, _source: array} $hit */
        foreach ($result['hits']['hits'] as $hit) {
            $responses[] = $this->documentParser->parse($hit);
        }
        $queryResult = new QueryResult($responses);
        $products = new Pagerfanta(new ElasticsearchAdapter($queryResult));

        return $this->render('@LRuozzi9SyliusElasticsearchPlugin/Product/index.html.twig', [
            'taxon' => $taxon,
            'products' => $products,
        ]);
    }
}
