<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Helper;

final class SortHelper implements SortHelperInterface
{
    public function retrieveSearchSorting(array $sortingQueryParams = []): array
    {
        return $sortingQueryParams;
    }

    public function retrieveTaxonSorting(array $sortingQueryParams = []): array
    {
        if ($sortingQueryParams === []) {
            return ['position' => 'asc'];
        }

        return $sortingQueryParams;
    }
}
