<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ProductTaxonDocumentParserEvent extends Event
{
    /**
     * @param array{taxon: array{sylius-id: int|string, code: ?string, name: array<array-key, array<string, string>>}, position: ?int} $esProductTaxon
     */
    public function __construct(
        private readonly array $esProductTaxon,
        private ProductTaxonInterface $productTaxon,
        private ProductInterface $product,
    ) {
    }

    /**
     * @return array{taxon: array{sylius-id: int|string, code: ?string, name: array<array-key, array<string, string>>}, position: ?int}
     */
    public function getEsProductTaxon(): array
    {
        return $this->esProductTaxon;
    }

    public function getProductTaxon(): ProductTaxonInterface
    {
        return $this->productTaxon;
    }

    public function setProductTaxon(ProductTaxonInterface $productTaxon): void
    {
        $this->productTaxon = $productTaxon;
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    public function setProduct(ProductInterface $product): void
    {
        $this->product = $product;
    }
}
