<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Mapper;

use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\OptionFilter;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResult;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResultInterface;
use Webgriffe\SyliusElasticsearchPlugin\Parser\DocumentParserInterface;

/**
 * @psalm-import-type ESDefaultOptionAggregation from ClientInterface
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

        $aggregations = [];
        if (array_key_exists('aggregations', $queryResult)) {
            $aggregations = $queryResult['aggregations'];
        }
        foreach ($aggregations as $aggregationKey => $rawAggregationData) {
            if (!array_key_exists('meta', $rawAggregationData) ||
                !is_array($rawAggregationData['meta']) ||
                !array_key_exists('type', $rawAggregationData['meta']) ||
                !is_string($rawAggregationData['meta']['type'])
            ) {
                continue;
            }
            $filterType = $rawAggregationData['meta']['type'];
            if ($filterType === OptionFilter::TYPE) {
                /** @var ESDefaultOptionAggregation $rawOptionAggregationData */
                $rawOptionAggregationData = $rawAggregationData;
                $filters[] = OptionFilter::resolveFromRawData($aggregationKey, $rawOptionAggregationData);
            }
        }

        return new QueryResult(
            $queryResult['hits']['total']['value'],
            $responses,
            $filters,
        );
    }
}
