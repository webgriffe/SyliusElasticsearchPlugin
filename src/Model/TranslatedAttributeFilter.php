<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;

/**
 * @psalm-import-type ESDefaultTranslatedAttributeAggregation from ClientInterface
 */
final readonly class TranslatedAttributeFilter extends Filter
{
    public const TYPE = 'translated-attribute';

    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @phpstan-ignore-next-line
     *
     * @param ESDefaultTranslatedAttributeAggregation $rawData
     *
     * @return TranslatedAttributeFilter[]
     */
    public static function resolveFromRawData(array $rawData): array
    {
        $filters = [];
        foreach ($rawData['filtered-translated-attributes']['translated-attribute']['buckets'] as $bucket) {
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
