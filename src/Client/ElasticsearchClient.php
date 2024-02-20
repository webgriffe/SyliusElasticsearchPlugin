<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Client;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\BulkException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\CreateIndexException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\RemoveIndexesException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\SwitchAliasException;
use LRuozzi9\SyliusElasticsearchPlugin\ClientBuilder\Exception\ClientConnectionException;
use Psr\Log\LoggerInterface;
use Throwable;

final class ElasticsearchClient implements ClientInterface
{
    private ?LoggerInterface $logger = null;

    private ?Client $client = null;

    public function __construct(
        private readonly string $host,
        private readonly string $port,
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

    public function createIndex(string $name, array $mappings): void
    {
        $params = [
            'index' => $name,
            'body' => ['mappings' => $mappings],
        ];

        try {
            /** @var array{acknowledged: bool, shards_acknowledged: bool, index: string} $response */
            $response = $this->getClient()->indices()->create($params);
        } catch (Throwable $e) {
            throw new CreateIndexException(
                'An error occurred while creating the index.',
                (int) $e->getCode(),
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

        $count = 0;
        /** @var array $action */
        foreach ($actions as $action) {
            ++$count;
            $params['body'][] = [
                'index' => [
                    '_index' => $indexName,
                ],
            ];

            $params['body'][] = $action;

            // Every 1000 actions stop and send the bulk request
            if ($count % 1000 === 0) {
                /** @var array{took: int, errors: bool, items: array} $result */
                $result = $this->getClient()->bulk($params);
                if ($result['errors'] === true) {
                    $this->getLogger()?->error('An error occurred while populating the index.', $result);

                    throw new BulkException('An error occurred while populating the index. Check logs for more information.');
                }

                // erase the old bulk request
                $params = ['body' => []];

                // unset the bulk response when you are done to save memory
                unset($result);
                $count = 0;
            }
        }

        // Send the last batch if it exists
        if ($params['body'] !== []) {
            /** @var array{took: int, errors: bool, items: array} $result */
            $result = $this->getClient()->bulk($params);
            if ($result['errors'] === true) {
                $this->getLogger()?->error('An error occurred while populating the index.', $result);

                throw new BulkException('An error occurred while populating the index. Check logs for more information.');
            }
        }
    }

    public function switchAlias(string $aliasName, string $toIndexName): void
    {
        $aliasExists = $this->getClient()->indices()->existsAlias([
            'name' => $aliasName,
        ]);

        if (!$aliasExists) {
            /** @var array{acknowledged: bool} $result */
            $result = $this->getClient()->indices()->putAlias([
                'name' => $aliasName,
                'index' => $toIndexName,
            ]);
            if ($result['acknowledged'] !== true) {
                throw new SwitchAliasException('The alias was not created. Acknowledged is not true.');
            }

            return;
        }
        /** @var array<string, array{aliases: array<string, array>}> $currentIndexes */
        $currentIndexes = $this->getClient()->indices()->getAlias([
            'name' => $aliasName,
        ]);
        $actions = [];
        foreach (array_keys($currentIndexes) as $indexName) {
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
        $result = $this->getClient()->indices()->updateAliases([
            'body' => [
                'actions' => $actions,
            ],
        ]);
        if ($result['acknowledged'] !== true) {
            throw new SwitchAliasException('Switch of alias not performed. Acknowledged is not true.');
        }
    }

    public function removeIndexes(string $wildcard = null, array $skips = []): void
    {
        /** @var array<string, array> $indexesToDelete */
        $indexesToDelete = $this->getClient()->indices()->get(['index' => $wildcard ?? '_all']);
        $indexesToDelete = array_diff(array_keys($indexesToDelete), $skips);

        foreach ($indexesToDelete as $indexName) {
            try {
                /** @var array{acknowledged: bool} $result */
                $result = $this->getClient()->indices()->delete(['index' => $indexName]);
            } catch (Throwable $e) {
                throw new RemoveIndexesException(
                    'An error occurred while removing the indexes.',
                    (int) $e->getCode(),
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
        /** @var array{took: int, timed_out: bool, _shards: array, hits: array{total: array, max_score: ?int, hits: array}} $results */
        $results = $this->getClient()->search([
            'index' => $indexName,
            'body' => $query,
        ]);

        return $results;
    }

    private function getClient(): Client
    {
        if ($this->client === null) {
            try {
                $this->client = ClientBuilder::create()
                    ->setHosts([$this->host . ':' . $this->port])
                    ->build();
            } catch (Throwable $e) {
                throw new ClientConnectionException(
                    'Could not connect to Elasticsearch server',
                    (int) $e->getCode(),
                    $e,
                );
            }
        }

        return $this->client;
    }
}
