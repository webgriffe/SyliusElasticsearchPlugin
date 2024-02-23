<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

interface QueryResultInterface
{
    public function getTotalHits(): int;

    /**
     * @return array<ResponseInterface>
     */
    public function getHints(int $offset, int $length): array;
}
