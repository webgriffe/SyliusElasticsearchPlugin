<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Model;

final readonly class QueryResult implements QueryResultInterface
{
    /**
     * @param ResponseInterface[] $hints
     */
    public function __construct(
        private array $hints,
    ) {
    }

    public function getTotalHits(): int
    {
        return count($this->hints);
    }

    public function getHints(int $offset, int $length): array
    {
        return $this->hints;
    }
}
