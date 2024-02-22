<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Client;

use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\BulkException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\CreateIndexException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\RemoveIndexesException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\SwitchAliasException;
use Psr\Log\LoggerAwareInterface;

interface ClientInterface extends LoggerAwareInterface
{
    /**
     * @throws CreateIndexException
     */
    public function createIndex(string $name, array $mappings): void;

    /**
     * @throws SwitchAliasException
     */
    public function switchAlias(string $aliasName, string $toIndexName): void;

    /**
     * @param string[] $skips List of indexes to skip from removal
     *
     * @throws RemoveIndexesException
     */
    public function removeIndexes(string $wildcard = null, array $skips = []): void;

    /**
     * @throws BulkException
     */
    public function bulk(string $indexName, array $actions): void;

    /**
     * @param string[] $indexes
     *
     * @return array{took: int, timed_out: bool, _shards: array, hits: array{total: array, max_score: ?int, hits: array}}
     */
    public function query(
        array $query,
        array $indexes = [],
        ?string $timeout = null,
    ): array;
}
