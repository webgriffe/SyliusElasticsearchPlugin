<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Twig;

use Sylius\Bundle\CoreBundle\Templating\Helper\VariantResolverHelper;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\ProductResponseInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\ProductVariantResponseInterface;

final readonly class VariantResolverRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private VariantResolverHelper $helper
    ) {
    }

    /**
     * @TODO: Extract this in a custom service to be customizable
     */
    public function resolveVariant(ProductInterface|ProductResponseInterface $product): null|ProductVariantResponseInterface|ProductVariantInterface
    {
        if ($product instanceof ProductInterface) {
            return $this->helper->resolveVariant($product);
        }
        $variants = $product->getEnabledVariants();
        if ($variants->isEmpty()) {
            return null;
        }
        $variants = $variants->toArray();
        usort(
            $variants,
            static fn (ProductVariantResponseInterface $a, ProductVariantResponseInterface $b) => $a->getPosition() <=> $b->getPosition(),
        );

        return reset($variants);
    }
}
