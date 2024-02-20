<?php

declare(strict_types=1);

namespace Tests\LRuozzi9\SyliusElasticsearchPlugin\Behat\Page\Shop;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

class StaticWelcomePage extends SymfonyPage implements WelcomePageInterface
{
    /**
     * @inheritdoc
     */
    public function getGreeting(): string
    {
        return $this->getElement('greeting')->getText();
    }

    /**
     * @inheritdoc
     */
    public function getRouteName(): string
    {
        return 'lruozzi9_sylius_elasticsearch_static_welcome';
    }

    /**
     * @inheritdoc
     */
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'greeting' => '#greeting',
        ]);
    }
}
