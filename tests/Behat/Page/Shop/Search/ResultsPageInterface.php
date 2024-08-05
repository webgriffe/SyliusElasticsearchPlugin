<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Page\Shop\Search;

use FriendsOfBehat\PageObjectExtension\Page\PageInterface;

interface ResultsPageInterface extends PageInterface
{
    public function countResults(): int;

    public function isBadRequest(): bool;
}
