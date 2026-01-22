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

    #[\Override]
    public function getTotalHits(): int
    {
        return $this->totalHints;
    }

    #[\Override]
    public function getHints(int $offset, int $length): array
    {
        return $this->hints;
    }

    #[\Override]
    public function getFilters(): array
    {
        return $this->filters;
    }
}
