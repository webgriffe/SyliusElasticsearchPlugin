<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Mapper;

use Webgriffe\SyliusElasticsearchPlugin\Model\Filter;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResult;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResultInterface;
use Webgriffe\SyliusElasticsearchPlugin\Parser\DocumentParserInterface;

final readonly class QueryResultMapper implements QueryResultMapperInterface
{
    public function __construct(
        private DocumentParserInterface $documentParser,
    ) {
    }

    public function map(array $queryResult): QueryResultInterface
    {
        $responses = [];
        /** @var array{_index: string, _id: string, score: float, _source: array} $hit */
        foreach ($queryResult['hits']['hits'] as $hit) {
            $responses[] = $this->documentParser->parse($hit);
        }
        $filters = [];
        /**
         * @var string $attributeCode
         * @var array{doc_count: int, values: array{doc_count: int, valu: array{doc_count_error_upper_bound: int, sum_other_doc_count: int, buckets: array<int, array{key: string, doc_count: int}>}}} $aggregation
         */
        foreach ($queryResult['aggregations'] as $attributeCode => $aggregation) {
            $buckets = $aggregation['values']['valu']['buckets'];
            if ($buckets === []) {
                continue;
            }
            $values = array_map(
                static fn (array $bucket): array => ['value' => $bucket['key'], 'count' => $bucket['doc_count']],
                $buckets,
            );
            $filters[] = new Filter($attributeCode, $values);
        }

        return new QueryResult(
            $queryResult['hits']['total']['value'],
            $responses,
            $filters,
        );
    }
}
