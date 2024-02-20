<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Manager;

use LRuozzi9\SyliusElasticsearchPlugin\ClientBuilder\ClientBuilderInterface;
use LRuozzi9\SyliusElasticsearchPlugin\DocumentType\DocumentTypeInterface;
use LRuozzi9\SyliusElasticsearchPlugin\Model\ProductResponse;
use LRuozzi9\SyliusElasticsearchPlugin\Model\QueryResult;
use LRuozzi9\SyliusElasticsearchPlugin\Model\QueryResultInterface;

final readonly class ElasticsearchIndexManager implements IndexManagerInterface
{
    public function __construct(
        private ClientBuilderInterface $clientBuilder,
    ) {
    }

    public function create(string $indexName, DocumentTypeInterface $documentType): void
    {
        $esClient = $this->clientBuilder->build();

        $esClient->createIndex(
            $indexName,
            ['mappings' => $documentType->getMappings()],
        );
    }

    public function populate(string $indexName, DocumentTypeInterface $documentType): void
    {
        $esClient = $this->clientBuilder->build();

        $esClient->bulk(
            $indexName,
            $documentType->getDocuments(),
        );
    }

    public function switchAlias(string $aliasName, string $toIndexName): void
    {
        $esClient = $this->clientBuilder->build();

        $esClient->switchAlias(
            $aliasName,
            $toIndexName,
        );
    }

    public function removeIndexes(string $wildcard, array $skips = []): void
    {
        $esClient = $this->clientBuilder->build();

        $esClient->removeIndexes($wildcard, $skips);
    }

    public function query(array $query, ?string $indexName = null): QueryResultInterface
    {
        $esClient = $this->clientBuilder->build();

        $result = $esClient->query($query, $indexName);
        $hints = [];
        foreach ($result['hits']['hits'] as $hit) {
            $hints[] = new ProductResponse(
                'sylius_product_show',
                ['slug' => 'TODO'],
            );
        }

        return new QueryResult($hints);
    }
}
