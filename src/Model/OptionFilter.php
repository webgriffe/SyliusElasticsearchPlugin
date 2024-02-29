<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;

/**
 * @psalm-import-type ESDefaultOptionAggregation from ClientInterface
 */
final readonly class OptionFilter extends Filter
{
    public const TYPE = 'option';

    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @phpstan-ignore-next-line
     *
     * @param ESDefaultOptionAggregation $rawData
     */
    public static function resolveFromRawData(string $aggregationKey, array $rawData): ?self
    {
        $optionValueCodeBuckets = $rawData['filter_by_key']['values']['values']['buckets'];
        if ($optionValueCodeBuckets === []) {
            return null;
        }
        $optionValueLabelBuckets = $rawData['filter_by_key']['values']['name']['filter_value_name_by_locale']['values']['buckets'];
        $values = [];
        for ($i = 0, $iMax = count($optionValueCodeBuckets); $i < $iMax; ++$i) {
            $optionValueCodeBucket = $optionValueCodeBuckets[$i];
            $optionValueLabelBucket = $optionValueLabelBuckets[$i];
            $values[] = new FilterValue($optionValueCodeBucket['key'], $optionValueLabelBucket['key'], $optionValueCodeBucket['doc_count']);
        }
        $optionName = null;
        $optionNameBuckets = $rawData['filter_by_key']['name']['filter_name_by_locale']['values']['buckets'];
        if (count($optionNameBuckets) > 0) {
            $firstOptionNameBucket = reset($optionNameBuckets);
            $optionName = $firstOptionNameBucket['key'];
        }
        if ($optionName === null) {
            $optionName = $aggregationKey;
        }

        return new self(
            $aggregationKey,
            $optionName,
            $values,
        );
    }
}
