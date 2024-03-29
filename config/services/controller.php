<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Controller\ElasticsearchController;

return static function (ContainerConfigurator $containerConfigurator) {
    $parameters = $containerConfigurator->parameters();
    $parameters->set('webgriffe.sylius_elasticsearch_plugin.taxon_default_page_limit', 9);
    $parameters->set('webgriffe.sylius_elasticsearch_plugin.search_default_page_limit', 9);

    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.controller.elasticsearch', ElasticsearchController::class)
        ->args([
            service('sylius.repository.taxon'),
            service('sylius.context.locale'),
            service('webgriffe.sylius_elasticsearch_plugin.client'),
            service('sylius.context.channel'),
            service('webgriffe.sylius_elasticsearch_plugin.generator.index_name'),
            service('webgriffe.sylius_elasticsearch_plugin.provider.document_type'),
            service('form.factory'),
            service('webgriffe.sylius_elasticsearch_plugin.builder.query'),
            service('webgriffe.sylius_elasticsearch_plugin.mapper.query_result'),
            service('event_dispatcher'),
            service('webgriffe.sylius_elasticsearch_plugin.helper.sort'),
            param('webgriffe.sylius_elasticsearch_plugin.taxon_default_page_limit'),
            param('webgriffe.sylius_elasticsearch_plugin.search_default_page_limit'),
        ])
        ->call('setContainer', [service('service_container')])
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber')
    ;
};
