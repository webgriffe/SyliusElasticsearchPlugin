<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Helper;

use Sylius\Component\Core\Model\TaxonInterface;

interface SortHelperInterface
{
    /**
     * @param array<string, string> $sortingQueryParams
     *
     * @return array<string, string>
     */
    public function retrieveSearchSorting(array $sortingQueryParams = []): array;

    /**
     * @param array<string, string> $sortingQueryParams
     *
     * @return array<string, string>
     */
    public function retrieveTaxonSorting(array $sortingQueryParams = [], ?TaxonInterface $taxon = null): array;
}
