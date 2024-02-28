<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Mapper;

use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\Filter;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResult;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResultInterface;
use Webgriffe\SyliusElasticsearchPlugin\Parser\DocumentParserInterface;

/**
 * @psalm-import-type ESAggregation from ClientInterface
 */
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
            $buckets = $aggregation['values']['valu']['buckets'];
            if ($buckets === []) {
                continue;
            }
            $values = array_map(
                static fn (array $bucket): array => ['value' => $bucket['key'], 'count' => $bucket['doc_count']],
                $buckets,
            );
            $filters[] = new Filter(
                $aggregationKey,
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
