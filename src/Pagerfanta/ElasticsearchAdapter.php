<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResultInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\ResponseInterface;

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
