<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Helper;

use Sylius\Component\Core\Model\TaxonInterface;

final class SortHelper implements SortHelperInterface
{
    public function retrieveSearchSorting(array $sortingQueryParams = []): array
    {
        return $sortingQueryParams; // If empty it will sort by _score desc as default
    }

    public function retrieveTaxonSorting(array $sortingQueryParams = [], TaxonInterface $taxon): array
    {
        if ($sortingQueryParams === []) {
            return ['position' => 'asc'];
        }

        return $sortingQueryParams;
    }
}
