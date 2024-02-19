<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Manager;

use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\CreateIndexException;
use LRuozzi9\SyliusElasticsearchPlugin\DocumentType\DocumentTypeInterface;

interface IndexManagerInterface
{
    /**
     * @param string $indexName The name of the index to create. It should be lowercase!
     */
    public function create(string $indexName, DocumentTypeInterface $documentType): void;

    public function populate(string $indexName, DocumentTypeInterface $documentType): void;

    public function switchAlias(string $aliasName, string $toIndexName): void;

    /**
     * @param string[] $skips List of indexes to skip from removal
     */
    public function removeIndexes(string $wildcard, array $skips = []): void;
}
