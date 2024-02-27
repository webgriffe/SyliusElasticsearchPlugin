<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

final readonly class QueryResult implements QueryResultInterface
{
    /**
     * @param ResponseInterface[] $hints
     * @param FilterInterface[] $filters
     */
    public function __construct(
        private int $totalHints,
        private array $hints,
        private array $filters,
    ) {
    }

    public function getTotalHits(): int
    {
        return $this->totalHints;
    }

    public function getHints(int $offset, int $length): array
    {
        return $this->hints;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }
}
