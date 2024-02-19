<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Model;

interface QueryResultInterface
{
    public function getTotalHits(): int;

    /**
     * @return array<ResponseInterface>
     */
    public function getHints(int $offset, int $length): array;
}
