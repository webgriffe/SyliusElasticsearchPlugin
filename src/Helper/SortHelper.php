<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Helper;

use Sylius\Component\Core\Model\TaxonInterface;

final class SortHelper implements SortHelperInterface
{
    #[\Override]
    public function retrieveSearchSorting(array $sortingQueryParams = []): array
    {
        return $sortingQueryParams; // If empty it will sort by _score desc as default
    }

    #[\Override]
    public function retrieveTaxonSorting(TaxonInterface $taxon, array $sortingQueryParams = []): array
    {
        if ($sortingQueryParams === []) {
            return ['position' => 'asc'];
        }

        return $sortingQueryParams;
    }
}
