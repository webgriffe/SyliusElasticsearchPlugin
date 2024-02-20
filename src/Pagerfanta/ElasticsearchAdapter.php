<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Pagerfanta;

use LRuozzi9\SyliusElasticsearchPlugin\Model\QueryResultInterface;
use LRuozzi9\SyliusElasticsearchPlugin\Model\ResponseInterface;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements AdapterInterface<ResponseInterface>
 */
final readonly class ElasticsearchAdapter implements AdapterInterface
{
    public function __construct(
        private QueryResultInterface $queryResult,
    ) {
    }

    public function getNbResults(): int
    {
        return max(0, $this->queryResult->getTotalHits());
    }

    public function getSlice(int $offset, int $length): iterable
    {
        return $this->queryResult->getHints($offset, $length);
    }
}
