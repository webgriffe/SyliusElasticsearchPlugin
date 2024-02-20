<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Client;

use Elasticsearch\Client;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\BulkException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\CreateIndexException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\RemoveIndexesException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\SwitchAliasException;
use Psr\Log\LoggerInterface;
use Throwable;

final class ElasticsearchClient implements ClientInterface
{
    private ?LoggerInterface $logger = null;

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function getLogger(): ?LoggerInterface
    {
        return $this->logger;
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

    public function bulk(string $indexName, array $actions): void
    {
        $params = ['body' => []];

        $count = 1;
        foreach ($actions as $action) {
            $params['body'][] = [
                'index' => [
                    '_index' => $indexName,
                ]
            ];

            $params['body'][] = $action;

            // Every 1000 actions stop and send the bulk request
            if ($count % 1000 === 0) {
                /** @var array{took: int, errors: bool, items: array} $result */
                $result = $this->client->bulk($params);
                if ($result['errors'] === true) {
                    $this->getLogger()?->error('An error occurred while populating the index.', $result);

                    throw new BulkException('An error occurred while populating the index. Check logs for more information.');
                }

                // erase the old bulk request
                $params = ['body' => []];

                // unset the bulk response when you are done to save memory
                unset($result);
            }
        }

        // Send the last batch if it exists
        if ($params['body'] !== []) {
            /** @var array{took: int, errors: bool, items: array} $result */
            $result = $this->client->bulk($params);
            if ($result['errors'] === true) {
                $this->getLogger()?->error('An error occurred while populating the index.', $result);

                throw new BulkException('An error occurred while populating the index. Check logs for more information.');
            }
        }
    }

    public function switchAlias(string $aliasName, string $toIndexName): void
    {
        $aliasExists = $this->client->indices()->existsAlias([
            'name' => $aliasName,
        ]);

        if (!$aliasExists) {
            /** @var array{acknowledged: bool} $result */
            $result = $this->client->indices()->putAlias([
                'name' => $aliasName,
                'index' => $toIndexName,
            ]);
            if ($result['acknowledged'] !== true) {
                throw new SwitchAliasException('The alias was not created. Acknowledged is not true.');
            }

            return;
        }
        /** @var array<string, array{aliases: array<string, array>}> $currentIndex */
        $currentIndexes = $this->client->indices()->getAlias([
            'name' => $aliasName,
        ]);
        $actions = [];
        foreach ($currentIndexes as $indexName => $aliases) {
            $actions[] = [
                'remove' => [
                    'index' => $indexName,
                    'alias' => $aliasName,
                ],
            ];
        }
        $actions[] = [
            'add' => [
                'index' => $toIndexName,
                'alias' => $aliasName,
            ],
        ];

        /** @var array{acknowledged: bool} $result */
        $result = $this->client->indices()->updateAliases([
            'body' => [
                'actions' => $actions,
            ]
        ]);
        if ($result['acknowledged'] !== true) {
            throw new SwitchAliasException('Switch of alias not performed. Acknowledged is not true.');
        }
    }

    public function removeIndexes(string $wildcard = null, array $skips = []): void
    {
        /** @var array<string, array> $indexesToDelete */
        $indexesToDelete = $this->client->indices()->get(['index' => $wildcard ?? '_all']);
        $indexesToDelete = array_diff(array_keys($indexesToDelete), $skips);

        foreach ($indexesToDelete as $indexName) {
            try {
                /** @var array{acknowledged: bool} $result */
                $result = $this->client->indices()->delete(['index' => $indexName]);
            } catch (Throwable $e) {
                throw new RemoveIndexesException(
                    'An error occurred while removing the indexes.',
                    $e->getCode(),
                    $e,
                );
            }
            if ($result['acknowledged'] !== true) {
                throw new RemoveIndexesException('Remove of indexes not completed. Acknowledged is not true.');
            }
        }
    }

    public function query(array $query, ?string $indexName = null): array
    {
        return $this->client->search([
            'index' => $indexName,
            'body' => $query,
        ]);
    }
}
