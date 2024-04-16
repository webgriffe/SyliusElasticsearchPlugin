<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Builder;

use Sylius\Component\Core\Model\TaxonInterface;

/**
 * @psalm-type QueryFilters = array{attribute: array<array-key, array{code: string, value: string}>, translated-attribute: array<array-key, array{code: string, value: string}>, option: array<array-key, array{code: string, value: string}>}
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

    /**
     * @param int<1, 10_000> $size
     * @param array<string, string> $sorting
     * @param QueryFilters $filters
     */
    public function buildSearchQuery(
        string $searchTerm,
        ?int $from = null,
        ?int $size = null,
        ?array $sorting = null,
        bool $withAggregates = false,
        ?array $filters = null,
        ?float $minScore = null,
    ): array;

    public function buildCompletionSuggestersQuery(
        string $searchTerm,
        ?string $source = 'suggest',
    ): array;
}
