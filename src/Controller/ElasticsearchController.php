<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Controller;

use LRuozzi9\SyliusElasticsearchPlugin\Model\QueryResult;
use LRuozzi9\SyliusElasticsearchPlugin\Pagerfanta\ElasticsearchAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ElasticsearchController extends AbstractController
{
    public function __construct(
        private readonly TaxonRepositoryInterface $taxonRepository,
        private readonly LocaleContextInterface $localeContext,
        private readonly ProductRepositoryInterface $productRepository,
    ) {
    }

    public function taxonAction(string $slug): Response
    {
        $localeCode = $this->localeContext->getLocaleCode();
        $taxon = $this->taxonRepository->findOneBySlug($slug, $localeCode);
        if (!$taxon instanceof TaxonInterface) {
            throw $this->createNotFoundException();
        }
        $products = $this->productRepository->findByTaxon($taxon);

        $products = new Pagerfanta(new ElasticsearchAdapter(new QueryResult()));

        return $this->render('@LRuozzi9SyliusElasticsearchPlugin/Product/index.html.twig', [
            'taxon' => $taxon,
            'products' => $products,
        ]);
    }
}
