<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class FilterExtension extends AbstractExtension
{
    /**
     * @psalm-suppress InvalidArgument
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('is_filter_active', [FilterRuntime::class, 'isFilterActive']),
        ];
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('merge_filter_value_with_current_active_filters', [FilterRuntime::class, 'mergeFilterValueWithCurrentActiveFilters']),
        ];
    }
}
