<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ProductOptionValueDocumentParserEvent extends Event
{
    /**
     * @param array{sylius-id: int|string, code: string, value: string, name: array<array-key, array<string, string>>} $esProductOptionValue
     */
    public function __construct(
        private readonly array $esProductOptionValue,
        private ProductOptionValueInterface $productOptionValue,
        private ProductOptionInterface $productOption,
        private ProductInterface $product,
    ) {
    }

    /**
     * @return array{sylius-id: int|string, code: string, value: string, name: array<array-key, array<string, string>>}
     */
    public function getEsProductOptionValue(): array
    {
        return $this->esProductOptionValue;
    }

    public function getProductOptionValue(): ProductOptionValueInterface
    {
        return $this->productOptionValue;
    }

    public function setProductOptionValue(ProductOptionValueInterface $productOptionValue): void
    {
        $this->productOptionValue = $productOptionValue;
    }

    public function getProductOption(): ProductOptionInterface
    {
        return $this->productOption;
    }

    public function setProductOption(ProductOptionInterface $productOption): void
    {
        $this->productOption = $productOption;
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
