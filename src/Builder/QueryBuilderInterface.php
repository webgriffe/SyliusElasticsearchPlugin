<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Builder;

use Sylius\Component\Core\Model\TaxonInterface;

/**
 * @psalm-type QueryFilters = array{attributes: array<array-key, array{code: string, value: string}>, options: array<array-key, array{code: string, value: string}>}
 */
interface QueryBuilderInterface
{
    /**
     * @param int<1, 10_000> $size
     * @param array<string, string> $sorting
     * @param QueryFilters $filters
     */
    public function buildTaxonQuery(
        TaxonInterface $taxon,
        ?int $from = null,
        ?int $size = null,
        ?array $sorting = null,
        bool $withAggregates = false,
        ?array $filters = null,
    ): array;
}
