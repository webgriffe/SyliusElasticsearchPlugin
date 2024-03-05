<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Helper;

interface SortHelperInterface
{
    /**
     * @param array<string, string> $sortingQueryParams
     *
     * @return array<string, string>
     */
    public function retrieveSorting(array $sortingQueryParams = []): array;
}
