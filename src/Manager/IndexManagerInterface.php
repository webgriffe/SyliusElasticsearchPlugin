<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Manager;

use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\CreateIndexException;

interface IndexManagerInterface
{
    /**
     * @param string $indexName The name of the index to create. It should be lowercase!
     *
     * @throws CreateIndexException
     */
    public function create(string $indexName, array $body): void;
}
