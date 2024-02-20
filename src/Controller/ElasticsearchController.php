<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Controller;

use LRuozzi9\SyliusElasticsearchPlugin\Client\ClientInterface;
use LRuozzi9\SyliusElasticsearchPlugin\DocumentType\ProductDocumentType;
use LRuozzi9\SyliusElasticsearchPlugin\Generator\IndexNameGeneratorInterface;
use LRuozzi9\SyliusElasticsearchPlugin\Model\QueryResult;
use LRuozzi9\SyliusElasticsearchPlugin\Pagerfanta\ElasticsearchAdapter;
use LRuozzi9\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class ElasticsearchController extends AbstractController
{
    public function __construct(
        private readonly TaxonRepositoryInterface $taxonRepository,
        private readonly LocaleContextInterface $localeContext,
        private readonly ClientInterface $indexManager,
        private readonly ChannelContextInterface $channelContext,
        private readonly IndexNameGeneratorInterface $indexNameGenerator,
        private readonly DocumentTypeProviderInterface $documentTypeProvider,
    ) {
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

        $result = $this->indexManager->query([
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
        ], $this->indexNameGenerator->generateAlias($channel, $this->documentTypeProvider->getDocumentType(ProductDocumentType::CODE)));
        $products = new Pagerfanta(new ElasticsearchAdapter(new QueryResult($result['hits']['hits'])));

        return $this->render('@LRuozzi9SyliusElasticsearchPlugin/Product/index.html.twig', [
            'taxon' => $taxon,
            'products' => $products,
        ]);
    }
}
