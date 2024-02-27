<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Builder;

use Sylius\Component\Core\Model\TaxonInterface;

interface QueryBuilderInterface
{
    /**
     * @param array<string, string> $sorting
     */
    public function buildTaxonQuery(
        TaxonInterface $taxon,
        array $sorting = [],
    ): array;
}
