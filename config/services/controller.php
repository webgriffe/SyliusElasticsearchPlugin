<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use LRuozzi9\SyliusElasticsearchPlugin\Controller\ElasticsearchController;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('lruozzi9.sylius_elasticsearch_plugin.controller.elasticsearch', ElasticsearchController::class)
        ->args([
            service('sylius.repository.taxon'),
            service('sylius.context.locale'),
            service('sylius.repository.product'),
        ])
        ->call('setContainer', [service('service_container')])
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber')
    ;
};
