<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Mapper;

use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResultInterface;

/**
 * @psalm-import-type ESQueryResult from ClientInterface
 */
interface QueryResultMapperInterface
{
    /**
     * @param ESQueryResult $queryResult
     */
    public function map(array $queryResult): QueryResultInterface;
}
