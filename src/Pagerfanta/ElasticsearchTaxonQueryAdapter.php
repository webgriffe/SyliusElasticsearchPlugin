<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Pagerfanta;

use Sylius\Component\Core\Model\TaxonInterface;
use Webgriffe\SyliusElasticsearchPlugin\Builder\QueryBuilderInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\Mapper\QueryResultMapperInterface;

/**
 * @psalm-import-type QueryFilters from QueryBuilderInterface
 */
final class ElasticsearchTaxonQueryAdapter extends AbstractElasticsearchQueryAdapter
{
    /**
     * @param array<array-key, string> $indexes
     * @param array<string, string> $sorting
     * @param QueryFilters $filters
     */
    public function __construct(
        private readonly QueryBuilderInterface $queryBuilder,
        ClientInterface $client,
        QueryResultMapperInterface $queryResultMapper,
        array $indexes,
        private readonly array $sorting,
        private readonly array $filters,
        private readonly TaxonInterface $taxon,
    ) {
        parent::__construct($client, $queryResultMapper, $indexes);
    }

    protected function getMinScore(): float
    {
        return 0;
    }

    protected function getCountQuery(): array
    {
        return $this->queryBuilder->buildTaxonQuery(
            $this->taxon,
            null,
            null,
            null,
            false,
            $this->filters,
        );
    }

    protected function getQuery(int $page, int $size): array
    {
        return $this->queryBuilder->buildTaxonQuery(
            $this->taxon,
            $page,
            $size,
            $this->sorting,
            true,
            $this->filters,
        );
    }
}
