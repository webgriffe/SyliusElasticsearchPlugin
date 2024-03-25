<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Ui\Shop\ProductContext;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.behat.context.ui.shop.product', ProductContext::class)
        ->public()
        ->args([
            service('webgriffe.sylius_elasticsearch_plugin.behat.page.shop.product.index'),
        ])
    ;
};
