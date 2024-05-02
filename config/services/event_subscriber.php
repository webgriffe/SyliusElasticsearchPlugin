<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\EventSubscriber\ProductEventSubscriber;
use Webgriffe\SyliusElasticsearchPlugin\EventSubscriber\ProductVariantSubscriber;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.event_subscriber.product', ProductEventSubscriber::class)
        ->args([
            service('webgriffe_sylius_elasticsearch_plugin.command_bus'),
            service('logger'),
            service('sylius.repository.channel'),
        ])
        ->tag('kernel.event_subscriber')
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.event_subscriber.product_variant', ProductVariantSubscriber::class)
        ->args([
            service('webgriffe_sylius_elasticsearch_plugin.command_bus'),
            service('logger'),
            service('sylius.repository.channel'),
        ])
        ->tag('kernel.event_subscriber')
    ;
};
