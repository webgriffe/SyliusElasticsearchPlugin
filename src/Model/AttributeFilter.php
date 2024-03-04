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
    public static function resolveFromRawData(array $rawData): array
    {
        $filters = [];
        foreach ($rawData['filtered_attributes']['attribute']['buckets'] as $bucket) {
            $attributeLabel = $bucket['key'];
            $attributeLabelBuckets = $bucket['label']['buckets'];
            $attributeLabelBucket = reset($attributeLabelBuckets);
            if ($attributeLabelBucket !== false) {
                $attributeLabel = $attributeLabelBucket['key'];
            }

            $attributeValues = [];
            foreach ($bucket['values']['buckets'] as $attributeValueBucket) {
                $attributeValueLabel = $attributeValueBucket['key'];
                // $attributeValueLabelBuckets = $attributeValueBucket['label']['buckets'];
                // $attributeValueLabelBucket = reset($attributeValueLabelBuckets);
                // if ($attributeValueLabelBucket !== false) {
                //     $attributeValueLabel = $attributeValueLabelBucket['key'];
                // }

                $attributeValues[] = new FilterValue(
                    $attributeValueBucket['key'],
                    $attributeValueLabel,
                    $attributeValueBucket['doc_count'],
                );
            }

            $filters[] = new self($bucket['key'], $attributeLabel, $attributeValues);
        }

        return $filters;
    }
}
