<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin;

use Webgriffe\SyliusElasticsearchPlugin\Builder\QueryBuilderInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\AttributeFilter;
use Webgriffe\SyliusElasticsearchPlugin\Model\OptionFilter;

/**
 * @psalm-import-type QueryFilters from QueryBuilderInterface
 */
final class FilterHelper
{
    /**
     * @param array<string, array<string, string>> $requestFilters
     *
     * @return QueryFilters
     */
    public static function retrieveFilters(array $requestFilters = []): array
    {
        $filters = [
            AttributeFilter::TYPE => [],
            OptionFilter::TYPE => [],
        ];

        foreach ($requestFilters as $type => $filter) {
            if (!array_key_exists($type, $filters)) {
                continue;
            }
            foreach ($filter as $code => $value) {
                $filters[$type][] = [
                    'code' => $code,
                    'value' => $value,
                ];
            }
        }

        return $filters;
    }
}
