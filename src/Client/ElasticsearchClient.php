<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Client;

use Elasticsearch\Client;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\CreateIndexException;
use Throwable;

final readonly class ElasticsearchClient implements ClientInterface
{
    public function __construct(
        private Client $client,
    ) {
    }

    public function createIndex(string $name, array $body): void
    {
        $params = [
            'index' => $name,
            'body' => $body,
        ];

        try {
            /** @var array{acknowledged: bool, shards_acknowledged: bool, index: string} $response */
            $response = $this->client->indices()->create($params);
        } catch (Throwable $e) {
            throw new CreateIndexException(
                'An error occurred while creating the index.',
                $e->getCode(),
                $e,
            );
        }

        if ($response['acknowledged'] !== true) {
            throw new CreateIndexException('The index was not created. Acknowledged is not true.');
        }
    }
}
