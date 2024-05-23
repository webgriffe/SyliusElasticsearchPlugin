<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ProductVariantDocumentParserEvent extends Event
{
    /**
     * @param array{sylius-id: int|string, code: ?string, enabled: ?bool, position: int, price: array{price: ?int, original-price: ?int, applied-promotions: array}, options: array, on-hand: ?int, on-hold: ?int, is-tracked: bool} $esProductImage
     */
    public function __construct(
        private readonly array $esProductImage,
        private ProductVariantInterface $productVariant,
        private ProductInterface $product,
    ) {
    }

    /**
     * @return array{sylius-id: int|string, code: ?string, enabled: ?bool, position: int, price: array{price: ?int, original-price: ?int, applied-promotions: array}, options: array, on-hand: ?int, on-hold: ?int, is-tracked: bool}
     */
    public function getEsProductImage(): array
    {
        return $this->esProductImage;
    }

    public function getProductVariant(): ProductVariantInterface
    {
        return $this->productVariant;
    }

    public function setProductVariant(ProductVariantInterface $productVariant): void
    {
        $this->productVariant = $productVariant;
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
