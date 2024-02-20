<?php

declare(strict_types=1);

namespace Tests\LRuozzi9\SyliusElasticsearchPlugin\Behat\Page\Shop;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface WelcomePageInterface extends SymfonyPageInterface
{
    public function getGreeting(): string;
}
