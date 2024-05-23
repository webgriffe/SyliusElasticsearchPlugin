<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser;

use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @psalm-type LocalizedField = array<array-key, array<string, string>>
 */
final class ProductDocumentParserEvent extends Event
{
    /**
     * @param array{sylius-id: int, code: string, name: LocalizedField, description: LocalizedField, short-description: LocalizedField, taxons: array, main_taxon: array, slug: LocalizedField, images: array, variants: array, product-options: array, translated-attributes: array, attributes: array} $esProduct
     */
    public function __construct(
        private readonly array $esProduct,
        private ProductInterface $product,
    ) {
    }

    /**
     * @return array{sylius-id: int, code: string, name: LocalizedField, description: LocalizedField, short-description: LocalizedField, taxons: array, main_taxon: array, slug: LocalizedField, images: array, variants: array, product-options: array, translated-attributes: array, attributes: array}
     */
    public function getEsProduct(): array
    {
        return $this->esProduct;
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
