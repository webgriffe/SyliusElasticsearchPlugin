<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser;

use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ProductImageDocumentParserEvent extends Event
{
    /**
     * @param array{path: ?string, type: ?string, variants: array} $esProductImage
     */
    public function __construct(
        private readonly array $esProductImage,
        private ProductImageInterface $productImage,
        private ProductInterface $product,
    ) {
    }

    /**
     * @return array{path: ?string, type: ?string, variants: array}
     */
    public function getEsProductImage(): array
    {
        return $this->esProductImage;
    }

    public function getProductImage(): ProductImageInterface
    {
        return $this->productImage;
    }

    public function setProductImage(ProductImageInterface $productImage): void
    {
        $this->productImage = $productImage;
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
