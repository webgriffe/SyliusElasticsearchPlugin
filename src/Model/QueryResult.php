<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

final readonly class QueryResult implements QueryResultInterface
{
    /**
     * @param ResponseInterface[] $hints
     */
    public function __construct(
        private int $totalHints,
        private array $hints,
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
}
