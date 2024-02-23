<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Twig;

use Sylius\Bundle\CoreBundle\Templating\Helper\PriceHelper;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\ProductVariantResponseInterface;
use Webmozart\Assert\Assert;

final readonly class PriceRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private PriceHelper $priceHelper,
    ) {
    }

    public function calculatePrice(ProductVariantInterface|ProductVariantResponseInterface $variant, array $context): int
    {
        if ($variant instanceof ProductVariantInterface) {
            return $this->priceHelper->getPrice($variant, $context);
        }
        $price = $variant->getPrice();
        Assert::integer($price);

        return $price;
    }

    public function calculateOriginalPrice(ProductVariantInterface|ProductVariantResponseInterface $variant, array $context): int
    {
        if ($variant instanceof ProductVariantInterface) {
            return $this->priceHelper->getOriginalPrice($variant, $context);
        }
        $originalPrice = $variant->getOriginalPrice();
        if (is_int($originalPrice)) {
            return $originalPrice;
        }

        return $this->calculatePrice($variant, $context);
    }

    public function hasDiscount(ProductVariantInterface|ProductVariantResponseInterface $variant, array $context): bool
    {
        if ($variant instanceof ProductVariantInterface) {
            return $this->priceHelper->hasDiscount($variant, $context);
        }
        $originalPrice = $this->calculateOriginalPrice($variant, $context);
        $price = $this->calculatePrice($variant, $context);

        return $originalPrice > $price;
    }
}
