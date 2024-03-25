<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Page\Shop\Product;

use Sylius\Behat\Page\Shop\Product\IndexPageInterface as BaseIndexPageInterface;

interface IndexPageInterface extends BaseIndexPageInterface
{
    public function hasFilter(string $filterName): bool;

    public function hasFilterWithValue(string $filterName, string $filterValue): bool;

    public function getFilterValueCounter(string $filterName, string $filterValue): int;

    public function filterBy(string $filterName, string $filterValue): void;
}
