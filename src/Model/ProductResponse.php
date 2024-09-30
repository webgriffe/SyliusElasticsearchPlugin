<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

use Sylius\Component\Core\Model\Product;

/**
 * @psalm-suppress PropertyNotSetInConstructor translations property is set in the parent class
 */
final class ProductResponse extends Product implements ProductResponseInterface
{
    public function getRouteName(): string
    {
        return 'sylius_shop_product_show';
    }

    public function getRouteParams(): array
    {
        return [
            'slug' => $this->getSlug(),
            '_locale' => $this->getTranslation()->getLocale(),
        ];
    }
}
