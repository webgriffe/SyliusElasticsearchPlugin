<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Ui\Shop\ProductContext;
use Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Ui\Shop\SearchContext;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.behat.context.ui.shop.product', ProductContext::class)
        ->public()
        ->args([
            service('webgriffe.sylius_elasticsearch_plugin.behat.page.shop.product.index'),
            service('sylius.behat.page.shop.product.show'),
            service('sylius.behat.shared_storage'),
        ])
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.behat.context.ui.shop.search', SearchContext::class)
        ->public()
        ->args([
            service('webgriffe.sylius_elasticsearch_plugin.behat.page.shop.search.results'),
            service('sylius.behat.shared_storage'),
        ])
    ;
};
