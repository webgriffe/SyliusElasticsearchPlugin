<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Page\Shop\Product;

use Sylius\Behat\Page\Shop\Product\IndexPage as BaseIndexPage;

final class IndexPage extends BaseIndexPage implements IndexPageInterface
{
    public function hasFilter(string $filterName): bool
    {
        return $this->hasElement('filter', ['%filterName%' => $filterName]);
    }

    public function hasFilterWithValue(string $filterName, string $filterValue): bool
    {
        return $this->hasElement('filter-value', ['%filterName%' => $filterName, '%filterValue%' => $filterValue]);
    }

    public function getFilterValueCounter(string $filterName, string $filterValue): int
    {
        $filterValueElement = $this->getElement('filter-value', ['%filterName%' => $filterName, '%filterValue%' => $filterValue]);

        return (int) str_replace([$filterValue, '(', ')'], '', $filterValueElement->getText());
    }

    public function filterBy(string $filterName, string $filterValue): void
    {
        $this->getElement('filter-value', ['%filterName%' => $filterName, '%filterValue%' => $filterValue])->click();
    }

    public function isBadRequest(): bool
    {
        $statusCode = $this->getSession()->getStatusCode();

        return $statusCode === 400;
    }

    protected function getDefinedElements(): array
    {
        return array_merge(
            parent::getDefinedElements(),
            [
                'filter' => '[data-test-filter-name="%filterName%"]',
                'filter-value' => '[data-test-filter-name="%filterName%"] [data-test-filter-value="%filterValue%"]',
            ],
        );
    }
}
