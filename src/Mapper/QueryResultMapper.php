<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Mapper;

use InvalidArgumentException;
use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\AttributeFilter;
use Webgriffe\SyliusElasticsearchPlugin\Model\OptionFilter;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResult;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResultInterface;
use Webgriffe\SyliusElasticsearchPlugin\Parser\DocumentParserInterface;

/**
 * @psalm-import-type ESDefaultOptionAggregation from ClientInterface
 * @psalm-import-type ESDefaultAttributeAggregation from ClientInterface
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
                throw new InvalidArgumentException(sprintf(
                    'Aggregation data for key "%s" is not valid. Please, provide a meta object with a type string.',
                    $aggregationKey,
                ));
            }
            $filterType = $rawAggregationData['meta']['type'];
            if ($filterType === OptionFilter::TYPE) {
                /** @var ESDefaultOptionAggregation $rawOptionAggregationData */
                $rawOptionAggregationData = $rawAggregationData;
                $filters = array_merge($filters, OptionFilter::resolveFromRawData($rawOptionAggregationData));
            }
            if ($filterType === AttributeFilter::TYPE) {
                /** @var ESDefaultAttributeAggregation $rawAttributeAggregationData */
                $rawAttributeAggregationData = $rawAggregationData;
                $filters = array_merge($filters, AttributeFilter::resolveFromRawData($rawAttributeAggregationData));
            }
        }

        return new QueryResult(
            $queryResult['hits']['total']['value'],
            $responses,
            $filters,
        );
    }
}
