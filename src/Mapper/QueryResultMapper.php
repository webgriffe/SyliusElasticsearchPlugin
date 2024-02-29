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
        foreach ($queryResult['hits']['hits'] as $hit) {
            $responses[] = $this->documentParser->parse($hit);
        }
        $filters = [];
        foreach ($queryResult['aggregations'] as $aggregationKey => $aggregation) {
            $buckets = $aggregation['filter_by_key']['values']['buckets'];
            if ($buckets === []) {
                continue;
            }
            $values = array_map(
                static fn (array $bucket): array => ['value' => $bucket['key'], 'count' => $bucket['doc_count']],
                $buckets,
            );
            $name = $aggregation['filter_by_key']['name']['filter_name_by_locale']['values']['buckets'][0]['key'];
            $filters[] = new Filter(
                $aggregationKey,
                $name,
                $aggregation['meta']['type'],
                $values,
            );
        }

        return new QueryResult(
            $queryResult['hits']['total']['value'],
            $responses,
            $filters,
        );
    }
}
