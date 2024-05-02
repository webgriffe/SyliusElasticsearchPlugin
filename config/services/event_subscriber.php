<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\EventSubscriber\ProductEventSubscriber;

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
};
