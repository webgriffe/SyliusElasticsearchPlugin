<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ProductAttributeDocumentParserEvent extends Event
{
    /**
     * @param array{sylius-id: int|string, code: string, type: string, storage-type: string, position: int, translatable: bool, filterable: bool, name: array<array-key, array<string, string>>, values: array} $esProductAttribute
     */
    public function __construct(
        private readonly array $esProductAttribute,
        private ProductAttributeInterface $productAttribute,
        private ProductInterface $product,
    ) {
    }

    /**
     * @return array{sylius-id: int|string, code: string, type: string, storage-type: string, position: int, translatable: bool, filterable: bool, name: array<array-key, array<string, string>>, values: array}
     */
    public function getEsProductAttribute(): array
    {
        return $this->esProductAttribute;
    }

    public function getProductAttribute(): ProductAttributeInterface
    {
        return $this->productAttribute;
    }

    public function setProductAttribute(ProductAttributeInterface $productAttribute): void
    {
        $this->productAttribute = $productAttribute;
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
