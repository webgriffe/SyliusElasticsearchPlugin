<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ProductAttributeValueDocumentParserEvent extends Event
{
    /**
     * @param array{sylius-id: int|string, code: string, locale: string, values: array<array-key, string>} $esProductAttributeValue
     */
    public function __construct(
        private readonly array $esProductAttributeValue,
        private ProductAttributeValueInterface $productAttributeValue,
        private ProductAttributeInterface $productAttribute,
        private ProductInterface $product,
    ) {
    }

    /**
     * @return array{sylius-id: int|string, code: string, locale: string, values: array<array-key, string>}
     */
    public function getEsProductAttributeValue(): array
    {
        return $this->esProductAttributeValue;
    }

    public function getProductAttributeValue(): ProductAttributeValueInterface
    {
        return $this->productAttributeValue;
    }

    public function setProductAttributeValue(ProductAttributeValueInterface $productAttributeValue): void
    {
        $this->productAttributeValue = $productAttributeValue;
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
