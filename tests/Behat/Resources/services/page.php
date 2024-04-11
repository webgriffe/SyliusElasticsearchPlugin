<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Page\Shop\Product\IndexPage;
use Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Page\Shop\Search\ResultsPage;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.behat.page.shop.product.index', IndexPage::class)
        ->parent('sylius.behat.page.shop.product.index')
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.behat.page.shop.search.results', ResultsPage::class)
        ->parent('sylius.behat.symfony_page')
    ;
};
