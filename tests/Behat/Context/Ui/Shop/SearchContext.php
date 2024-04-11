<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Sylius\Behat\Service\SharedStorageInterface;
use Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Page\Shop\Search\ResultsPageInterface;
use Webmozart\Assert\Assert;

final readonly class SearchContext implements Context
{
    public function __construct(
        private ResultsPageInterface $searchResultPage,
        private SharedStorageInterface $sharedStorage,
    ) {
    }

    /**
     * @When /^I search for "([^"]*)"$/
     */
    public function iSearchFor(string $searchTerm): void
    {
        $currentLocaleCode = $this->sharedStorage->get('current_locale_code');

        $this->searchResultPage->tryToOpen(['_locale' => $currentLocaleCode, 'query' => $searchTerm]);
    }

    /**
     * @Then /^I should see "([^"]*)" results$/
     */
    public function iShouldSeeResults(int $count): void
    {
        Assert::eq($this->searchResultPage->countResults(), $count);
    }
}
