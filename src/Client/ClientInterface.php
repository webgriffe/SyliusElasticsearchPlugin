<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Client;

use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\CreateIndexException;

interface ClientInterface
{
    /**
     * @throws CreateIndexException
     */
    public function createIndex(string $name, array $body): void;
}
