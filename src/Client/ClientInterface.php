<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Client;

use Psr\Log\LoggerAwareInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\Exception\BulkException;
use Webgriffe\SyliusElasticsearchPlugin\Client\Exception\CreateIndexException;
use Webgriffe\SyliusElasticsearchPlugin\Client\Exception\RemoveIndexesException;
use Webgriffe\SyliusElasticsearchPlugin\Client\Exception\SwitchAliasException;

/**
 * @psalm-type ESValuesAggregation = array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<int, array{key: string, doc_count: int}>}
 * @psalm-type ESDefaultTranslatedAttributeAggregation = array{meta: array{type: string}, doc_count: int, filtered-translated-attributes: array{doc_count: int, translated-attribute: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int, values: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int, label: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int}>}}>}, label: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int}>}}>}}}
 * @psalm-type ESDefaultAttributeAggregation = array{meta: array{type: string}, doc_count: int, filtered-attributes: array{doc_count: int, attribute: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int, values: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int, label: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int}>}}>}, label: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int}>}}>}}}
 * @psalm-type ESDefaultOptionAggregation = array{meta: array{type: string}, doc_count: int, filtered_options: array{doc_count: int, option: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int, values: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int, label: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int}>}}>}, label: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<array-key, array{key: string, doc_count: int}>}}>}}}
 * @psalm-type ESAggregation = array{meta: array{type: string}, doc_count: int, filter_by_key: array{doc_count: int, values: ESValuesAggregation, name: array{doc_count: int, filter_name_by_locale: array{doc_count: int, values: ESValuesAggregation}}}}
 * @psalm-type ESHit = array{_index: string, _id: string, score: float, _source: array}
 * @psalm-type ESQueryResult = array{took: int, timed_out: bool, _shards: array, hits: array{total: array{value: int, relation: string}, max_score: ?int, hits: array<array-key, ESHit>}, aggregations?: array<string, array|ESAggregation|ESDefaultOptionAggregation>}
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
     * @throws BulkException
     */
    public function bulk(string $indexName, array $actions): void;

    /**
     * @param string[] $indexes
     *
     * @return ESQueryResult
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
}
