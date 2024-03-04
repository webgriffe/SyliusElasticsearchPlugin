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
     *
     * @return OptionFilter[]
     */
    public static function resolveFromRawData(array $rawData): array
    {
        $filters = [];
        foreach ($rawData['filtered_options']['option']['buckets'] as $bucket) {
            $optionLabel = $bucket['key'];
            $optionLabelBuckets = $bucket['label']['buckets'];
            $optionLabelBucket = reset($optionLabelBuckets);
            if ($optionLabelBucket !== false) {
                $optionLabel = $optionLabelBucket['key'];
            }

            $optionValues = [];
            foreach ($bucket['values']['buckets'] as $optionValueBucket) {
                $optionValueLabel = $optionValueBucket['key'];
                $optionValueLabelBuckets = $optionValueBucket['label']['buckets'];
                $optionValueLabelBucket = reset($optionValueLabelBuckets);
                if ($optionValueLabelBucket !== false) {
                    $optionValueLabel = $optionValueLabelBucket['key'];
                }

                $optionValues[] = new FilterValue(
                    $optionValueBucket['key'],
                    $optionValueLabel,
                    $optionValueBucket['doc_count'],
                );
            }

            $filters[] = new self($bucket['key'], $optionLabel, $optionValues);
        }

        return $filters;
    }
}
