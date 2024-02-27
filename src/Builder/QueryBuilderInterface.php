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
        int $from = 0,
        int $size = 10,
        array $sorting = [],
    ): array;
}
