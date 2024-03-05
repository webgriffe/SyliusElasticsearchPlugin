<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Helper;

final class SortHelper implements SortHelperInterface
{
    public function retrieveSorting(array $sortingQueryParams = []): array
    {
        $sorting = [];
        if ($sortingQueryParams === []) {
            $sorting = ['position' => 'asc'];
        }

        return $sorting;
    }
}
