<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;

/**
 * @psalm-import-type ESAggregation from ClientInterface
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
     * @param ESAggregation $rawData
     */
    public static function resolveFromRawData(string $aggregationKey, array $rawData): ?FilterInterface
    {
        $values = [];
        $attributeValueCodeBuckets = $rawData['filter_by_key']['values']['buckets'];
        if ($attributeValueCodeBuckets === []) {
            return null;
        }
        $attributeValueLabelBuckets = $rawData['filter_by_key']['name']['filter_name_by_locale']['values']['buckets'];
        foreach ($attributeValueCodeBuckets as $value) {
            $values[] = new FilterValue($value['key'], $value['key'], $value['doc_count']);
        }
        $attributeName = $aggregationKey;
        $firstAttributeLabelBucket = reset($attributeValueLabelBuckets);
        if ($firstAttributeLabelBucket !== false) {
            $attributeName = $firstAttributeLabelBucket['key'];
        }

        return new self($aggregationKey, $attributeName, $values);
    }
}
