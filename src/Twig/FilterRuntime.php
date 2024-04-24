<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Twig;

use Twig\Extension\RuntimeExtensionInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\FilterInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\FilterValueInterface;

final class FilterRuntime implements RuntimeExtensionInterface
{
    /**
     * @param array<string, array<array-key, array{code: string, value: string}>> $activeFilters
     */
    public function isFilterActive(FilterValueInterface $filterValue, FilterInterface $filter, array $activeFilters): bool
    {
        if (!array_key_exists($filter->getType(), $activeFilters)) {
            return false;
        }
        $appliedFilterValues = $activeFilters[$filter->getType()];
        foreach ($appliedFilterValues as $appliedFilterValue) {
            if ($appliedFilterValue['code'] === $filter->getKeyCode() &&
                $appliedFilterValue['value'] === $filterValue->getKey()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * If the filter value is already applied, it will be removed from the active filters, so that it can be toggled.
     * Otherwise, it will be added to the active filters.
     *
     * @param array<string, array<array-key, array{code: string, value: string}>> $activeFilters
     */
    public function mergeFilterValueWithCurrentActiveFilters(
        FilterValueInterface $filterValue,
        FilterInterface $filter,
        array $activeFilters,
    ): array {
        $filterType = $filter->getType();
        if (!array_key_exists($filterType, $activeFilters)) {
            $activeFilters[$filterType] = [];
        }
        foreach ($activeFilters[$filterType] as $key => $appliedFilterValue) {
            if ($appliedFilterValue['code'] === $filter->getKeyCode() &&
                $appliedFilterValue['value'] === $filterValue->getKey()) {
                unset($activeFilters[$filterType][$key]);

                return $activeFilters;
            }
        }
        $activeFilters[$filterType][] = [
            'code' => $filter->getKeyCode(),
            'value' => $filterValue->getKey(),
        ];

        return $activeFilters;
    }
}
