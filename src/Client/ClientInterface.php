<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Client;

use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\CreateIndexException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\RemoveIndexesException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\SwitchAliasException;

interface ClientInterface
{
    /**
     * @throws CreateIndexException
     */
    public function createIndex(string $name, array $body): void;

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
}
