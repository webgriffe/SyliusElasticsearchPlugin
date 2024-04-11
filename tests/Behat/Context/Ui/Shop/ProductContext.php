<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Behat\Page\Shop\Product\ShowPageInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Page\Shop\Product\IndexPageInterface;
use Webmozart\Assert\Assert;

final readonly class ProductContext implements Context
{
    public function __construct(
        private IndexPageInterface $indexPage,
        private ShowPageInterface $showPage,
        private SharedStorageInterface $sharedStorage,
    ) {
    }

    /**
     * @Given /^I should see the filter "([^"]+)"$/
     */
    public function iShouldSeeTheFilter(string $filterName): void
    {
        Assert::true($this->indexPage->hasFilter($filterName));
    }

    /**
     * @Given /^I should see the value "([^"]*)" for filter "([^"]+)"$/
     * @Given /^I should see the value "([^"]*)" for filter "([^"]+)" with counter "([^"]+)"$/
     */
    public function iShouldSeeTheFilterValueFor(string $filterValue, string $filterName, int $counter = 1): void
    {
        Assert::true($this->indexPage->hasFilterWithValue($filterName, $filterValue));
        Assert::eq($this->indexPage->getFilterValueCounter($filterName, $filterValue), $counter);
    }

    /**
     * @When /^I filter products by "([^"]*)" with value "([^"]*)"$/
     */
    public function iFilterProductsByWithValue(string $filterName, string $filterValue): void
    {
        $this->indexPage->filterBy($filterName, $filterValue);
    }

    /**
     * @Then /^I should be redirected to the (product "[^"]+") page$/
     */
    public function iShouldBeRedirectedToTheProductPage(ProductInterface $product): void
    {
        $currentLocaleCode = $this->sharedStorage->get('current_locale_code');
        $productTranslation = $product->getTranslation($currentLocaleCode);

        Assert::true($this->showPage->isOpen(['_locale' => $currentLocaleCode, 'slug' => $productTranslation->getSlug()]));
    }
}
