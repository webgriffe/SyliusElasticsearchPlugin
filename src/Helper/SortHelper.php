<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Helper;

final class SortHelper implements SortHelperInterface
{
    public function retrieveSorting(array $sortingQueryParams = []): array
    {
        if ($sortingQueryParams === []) {
            return ['position' => 'asc'];
        }

        return $sortingQueryParams;
    }
}
