<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Pagerfanta;

use Webgriffe\SyliusElasticsearchPlugin\Builder\QueryBuilderInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\Mapper\QueryResultMapperInterface;

/**
 * @psalm-import-type QueryFilters from QueryBuilderInterface
 */
final class ElasticsearchSearchQueryAdapter extends AbstractElasticsearchQueryAdapter
{
    /**
     * @param array<array-key, string> $indexes
     * @param array<string, string> $sorting
     * @param QueryFilters $filters
     */
    public function __construct(
        private readonly QueryBuilderInterface $queryBuilder,
        ClientInterface $indexManager,
        QueryResultMapperInterface $queryResultMapper,
        array $indexes,
        private readonly array $sorting,
        private readonly array $filters,
        private readonly string $searchTerm,
    ) {
        parent::__construct($indexManager, $queryResultMapper, $indexes);
    }

    protected function getCountQuery(): array
    {
        return $this->queryBuilder->buildSearchQuery(
            $this->searchTerm,
            null,
            null,
            null,
            false,
            $this->filters,
        );
    }

    protected function getMinScore(): float
    {
        return 1;
    }

    protected function getQuery(int $page, int $size): array
    {
        return $this->queryBuilder->buildSearchQuery(
            $this->searchTerm,
            $page,
            $size,
            $this->sorting,
            true,
            $this->filters,
            $this->getMinScore(),
        );
    }
}
