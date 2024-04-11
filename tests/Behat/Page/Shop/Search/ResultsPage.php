<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Page\Shop\Search;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

final class ResultsPage extends SymfonyPage implements ResultsPageInterface
{
    public function getRouteName(): string
    {
        return 'sylius_shop_search';
    }

    public function countResults(): int
    {
        return count($this->getDocument()->findAll('css', '[data-test-result]'));
    }
}
