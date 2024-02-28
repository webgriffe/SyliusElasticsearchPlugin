<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Builder;

use Sylius\Component\Core\Model\TaxonInterface;

interface QueryBuilderInterface
{
    /**
     * @param array<string, string> $sorting
     * @param int<1, 10_000> $size
     */
    public function buildTaxonQuery(
        TaxonInterface $taxon,
        ?int $from = null,
        ?int $size = null,
        ?array $sorting = null,
        bool $withAggregates = false,
    ): array;
}
