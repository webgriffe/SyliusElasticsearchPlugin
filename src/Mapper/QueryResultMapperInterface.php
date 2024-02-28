<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Mapper;

use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResultInterface;

/**
 * @psalm-import-type QueryResult from \Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface
 */
interface QueryResultMapperInterface
{
    /**
     * @param QueryResult $queryResult
     */
    public function map(array $queryResult): QueryResultInterface;
}
