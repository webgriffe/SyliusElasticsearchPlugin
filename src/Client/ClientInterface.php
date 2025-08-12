<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Client;

use Generator;
use Psr\Log\LoggerAwareInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\Exception\BulkException;
use Webgriffe\SyliusElasticsearchPlugin\Client\Exception\CreateIndexException;
use Webgriffe\SyliusElasticsearchPlugin\Client\Exception\RemoveIndexesException;
use Webgriffe\SyliusElasticsearchPlugin\Client\Exception\SwitchAliasException;
use Webgriffe\SyliusElasticsearchPlugin\Client\ValueObject\BulkAction;

/**
 * @psalm-type ESValuesAggregation = array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<int, array{key: string, doc_count: int}>}
 * @psalm-type ESDefaultAttributeAggregation = array{meta: array{type: string}, doc_count: int, filtered-attributes: array{doc_count: int, attribute: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int, values: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int, label: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int}>}}>}, label: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int}>}}>}}}
 * @psalm-type ESDefaultOptionAggregation = array{meta: array{type: string}, doc_count: int, option: array{doc_count: int, filtered_options: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int, values: array{doc_count: int, value: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int, label: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int}>}}>}}, label: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int}>}}>}}}
 * @psalm-type ESAggregation = array{meta: array{type: string}, doc_count: int, filter_by_key: array{doc_count: int, values: ESValuesAggregation, name: array{doc_count: int, filter_name_by_locale: array{doc_count: int, values: ESValuesAggregation}}}}
 * @psalm-type ESHit = array{_index: string, _id: string, score: float, _source: array}
 * @psalm-type ESQueryResult = array{took: int, timed_out: bool, _shards: array, hits: array{total: array{value: int, relation: string}, max_score: ?int, hits: array<array-key, ESHit>}, aggregations?: array<string, array|ESAggregation|ESDefaultOptionAggregation>}
 * @psalm-type ESSuggestOption = array{text: string, _index: string, _type: string, _id: string, _score: float, _source: array{suggest: array<array-key, string>}}
 * @psalm-type ESCompletionSuggesters = array<string, array<array-key, array{text: string, offset: int, length: int, options: array<array-key, ESSuggestOption>}>>
 * @psalm-type ESTermSuggesters = array<string, array<array-key, array{text: string, offset: int, length: int, options: array<array-key, ESSuggestOption>}>>
 */
interface ClientInterface extends LoggerAwareInterface
{
    /**
     * @throws CreateIndexException
     */
    public function createIndex(string $name, array $mappings, array $settings): void;

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

    /**
     * @param BulkAction[] $actions
     *
     * @return Generator<array-key, int> Number of executed actions at each step
     *
     * @throws BulkException
     */
    public function bulk(string $indexName, array $actions): Generator;

    /**
     * @param string[] $indexes
     *
     * @return ESQueryResult
     *
     * @throws \Exception
     */
    public function query(
        array $query,
        array $indexes = [],
        ?string $timeout = null,
    ): array;

    /**
     * @param string[] $indexes
     */
    public function count(
        array $query,
        array $indexes = [],
        ?float $minScore = null,
    ): int;

    /**
     * @param string[] $indexes
     *
     * @return ESCompletionSuggesters
     */
    public function completionSuggesters(
        array $query,
        array $indexes = [],
    ): array;

    /**
     * @param string[] $indexes
     *
     * @return ESTermSuggesters
     */
    public function termSuggesters(
        array $query,
        array $indexes = [],
    ): array;

    public function existsAlias(string $aliasName): bool;
}
