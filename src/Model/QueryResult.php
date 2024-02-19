<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Model;

final class QueryResult implements QueryResultInterface
{
    public function getTotalHits(): int
    {
        return 0;
    }

    public function getHints(int $offset, int $length): array
    {
        return [];
    }
}
