<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin;

use Webgriffe\SyliusElasticsearchPlugin\Builder\QueryBuilderInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\AttributeFilter;
use Webgriffe\SyliusElasticsearchPlugin\Model\OptionFilter;
use Webgriffe\SyliusElasticsearchPlugin\Model\TranslatedAttributeFilter;

/**
 * @psalm-import-type QueryFilters from QueryBuilderInterface
 */
final class FilterHelper
{
    /**
     * @param array<string, array<array-key, array{code: string, value: string}>> $requestFilters
     *
     * @return QueryFilters
     */
    public static function retrieveFilters(array $requestFilters = []): array
    {
        $filters = [
            AttributeFilter::TYPE => [],
            TranslatedAttributeFilter::TYPE => [],
            OptionFilter::TYPE => [],
        ];

        foreach ($requestFilters as $type => $filter) {
            if (!array_key_exists($type, $filters)) {
                continue;
            }
            foreach ($filter as $value) {
                $filters[$type][] = [
                    'code' => $value['code'],
                    'value' => $value['value'],
                ];
            }
        }

        return $filters;
    }
}
