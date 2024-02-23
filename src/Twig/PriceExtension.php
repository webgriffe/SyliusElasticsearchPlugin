<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class PriceExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('sylius_calculate_price', [PriceRuntime::class, 'calculatePrice']),
            new TwigFilter('sylius_calculate_original_price', [PriceRuntime::class, 'calculateOriginalPrice']),
            new TwigFilter('sylius_has_discount', [PriceRuntime::class, 'hasDiscount']),
        ];
    }
}
