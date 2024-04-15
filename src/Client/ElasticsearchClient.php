<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Client;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Generator;
use Psr\Log\LoggerInterface;
use Throwable;
use Webgriffe\SyliusElasticsearchPlugin\Client\Exception\BulkException;
use Webgriffe\SyliusElasticsearchPlugin\Client\Exception\CreateIndexException;
use Webgriffe\SyliusElasticsearchPlugin\Client\Exception\RemoveIndexesException;
use Webgriffe\SyliusElasticsearchPlugin\Client\Exception\SwitchAliasException;
use Webgriffe\SyliusElasticsearchPlugin\ClientBuilder\Exception\ClientConnectionException;

/**
 * @psalm-import-type ESQueryResult from ClientInterface
 * @psalm-import-type ESCompletionSuggesters from ClientInterface
 */
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

    public function createIndex(string $name, array $mappings, array $settings): void
    {
        $params = [
            'index' => $name,
            'body' => [
                'mappings' => $mappings,
                'settings' => $settings,
            ],
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

    public function bulk(string $indexName, array $actions): Generator
    {
        $params = ['body' => []];

        $count = $totalCount = 0;
        /** @var array $action */
        foreach ($actions as $action) {
            ++$count;
            ++$totalCount;
            $params['body'][] = [
                'index' => [
                    '_index' => $indexName,
                ],
            ];

            $params['body'][] = $action;

            // Every 250 actions stop and send the bulk request
            if ($count % 250 === 0) {
                /** @var array{took: int, errors: bool, items: array} $result */
                $result = $this->getClient()->bulk($params);
                if ($result['errors'] === true) {
                    $this->getLogger()?->error('An error occurred while populating the index.', $result);

                    throw new BulkException('An error occurred while populating the index. Check logs for more information.');
                }
                yield $totalCount;

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

        yield $totalCount;
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

    public function query(
        array $query,
        array $indexes = [],
        ?string $timeout = null,
    ): array {
        /** @var ESQueryResult $results */
        $results = $this->getClient()->search([
            'index' => implode(',', $indexes),
            'body' => $query,
            'timeout' => $timeout,
        ]);

        return $results;
    }

    public function count(
        array $query,
        array $indexes = [],
        ?float $minScore = null,
    ): int {
        /** @var array{count: int, _shards: array} $result */
        $result = $this->getClient()->count([
            'index' => implode(',', $indexes),
            'body' => $query,
            'min_score' => $minScore,
        ]);

        return $result['count'];
    }

    public function completionSuggesters(
        array $query,
        array $indexes = [],
    ): array {
        /** @var array{took: int, timed_out: bool, _shards: array, hits: array, suggest: ESCompletionSuggesters} $result */
        $result = $this->getClient()->search([
            'index' => implode(',', $indexes),
            'body' => $query,
        ]);

        return $result['suggest'];
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
