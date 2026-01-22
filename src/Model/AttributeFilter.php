<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;

/**
 * @psalm-import-type ESDefaultAttributeAggregation from ClientInterface
 */
final readonly class AttributeFilter extends Filter
{
    public const TYPE = 'attribute';

    #[\Override]
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @phpstan-ignore-next-line
     *
     * @param ESDefaultAttributeAggregation $rawData
     *
     * @return AttributeFilter[]
     */
    #[\Override]
    public static function resolveFromRawData(array $rawData): array
    {
        $filters = [];
        foreach ($rawData['filtered-attributes']['attribute']['buckets'] as $bucket) {
            $attributeLabel = $bucket['key'];
            $attributeLabelBuckets = $bucket['label']['buckets'];
            $attributeLabelBucket = reset($attributeLabelBuckets);
            if ($attributeLabelBucket !== false) {
                $attributeLabel = $attributeLabelBucket['key'];
            }

            $attributeValues = [];
            foreach ($bucket['values']['buckets'] as $attributeValueBucket) {
                $attributeValues[] = new FilterValue(
                    $attributeValueBucket['key'],
                    $attributeValueBucket['key'],
                    $attributeValueBucket['doc_count'],
                );
            }

            $filters[] = new self($bucket['key'], $attributeLabel, $attributeValues);
        }

        return $filters;
    }
}
