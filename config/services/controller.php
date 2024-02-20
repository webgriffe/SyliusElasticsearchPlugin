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
            service('lruozzi9.sylius_elasticsearch_plugin.client'),
            service('sylius.context.channel'),
            service('lruozzi9.sylius_elasticsearch_plugin.generator.index_name'),
            service('lruozzi9.sylius_elasticsearch_plugin.provider.document_type'),
        ])
        ->call('setContainer', [service('service_container')])
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber')
    ;
};
