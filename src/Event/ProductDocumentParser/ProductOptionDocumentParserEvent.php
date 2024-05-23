<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ProductOptionDocumentParserEvent extends Event
{
    /**
     * @param array{sylius-id: int|string, code: string, name: array<array-key, array<string, string>>, position: int, filterable: bool, values: array} $esProductOption
     */
    public function __construct(
        private readonly array $esProductOption,
        private ProductOptionInterface $productOption,
        private ProductInterface $product,
    ) {
    }

    /**
     * @return array{sylius-id: int|string, code: string, name: array<array-key, array<string, string>>, position: int, filterable: bool, values: array}
     */
    public function getEsProductOption(): array
    {
        return $this->esProductOption;
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
