<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Manager;

use LRuozzi9\SyliusElasticsearchPlugin\ClientBuilder\ClientBuilderInterface;

final readonly class ElasticsearchIndexManager implements IndexManagerInterface
{
    public function __construct(
        private ClientBuilderInterface $clientBuilder,
    ) {
    }

    public function create(string $indexName, array $body): void
    {
        $esClient = $this->clientBuilder->build();

        $esClient->createIndex(
            $indexName,
            $body,
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
}
