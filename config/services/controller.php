<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Controller\InstantSearchController;
use Webgriffe\SyliusElasticsearchPlugin\Controller\InstantSearchControllerInterface;
use Webgriffe\SyliusElasticsearchPlugin\Controller\ProductController;
use Webgriffe\SyliusElasticsearchPlugin\Controller\ProductControllerInterface;
use Webgriffe\SyliusElasticsearchPlugin\Controller\SearchController;
use Webgriffe\SyliusElasticsearchPlugin\Controller\SearchControllerInterface;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.controller.search', SearchController::class)
        ->args([
            service('webgriffe.sylius_elasticsearch_plugin.client'),
            service('sylius.context.channel'),
            service('webgriffe.sylius_elasticsearch_plugin.generator.index_name'),
            service('webgriffe.sylius_elasticsearch_plugin.provider.document_type'),
            service('form.factory'),
            service('webgriffe.sylius_elasticsearch_plugin.builder.query'),
            service('webgriffe.sylius_elasticsearch_plugin.mapper.query_result'),
            service('webgriffe.sylius_elasticsearch_plugin.helper.sort'),
            service('webgriffe.sylius_elasticsearch_plugin.validator.request'),
        ])
        ->call('setContainer', [service('service_container')])
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber')
    ;
    $services->alias(SearchControllerInterface::class, 'webgriffe.sylius_elasticsearch_plugin.controller.search');

    $services->set('webgriffe.sylius_elasticsearch_plugin.controller.instant_search', InstantSearchController::class)
        ->args([
            service('webgriffe.sylius_elasticsearch_plugin.client'),
            service('sylius.context.channel'),
            service('webgriffe.sylius_elasticsearch_plugin.generator.index_name'),
            service('webgriffe.sylius_elasticsearch_plugin.provider.document_type'),
            service('webgriffe.sylius_elasticsearch_plugin.builder.query'),
            service('webgriffe.sylius_elasticsearch_plugin.mapper.query_result'),
            service('webgriffe.sylius_elasticsearch_plugin.helper.sort'),
        ])
        ->call('setContainer', [service('service_container')])
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber')
    ;
    $services->alias(InstantSearchControllerInterface::class, 'webgriffe.sylius_elasticsearch_plugin.controller.instant_search');

    $services->set('webgriffe.sylius_elasticsearch_plugin.controller.product', ProductController::class)
        ->args([
            service('sylius.repository.taxon'),
            service('sylius.context.locale'),
            service('webgriffe.sylius_elasticsearch_plugin.client'),
            service('sylius.context.channel'),
            service('webgriffe.sylius_elasticsearch_plugin.generator.index_name'),
            service('webgriffe.sylius_elasticsearch_plugin.provider.document_type'),
            service('webgriffe.sylius_elasticsearch_plugin.builder.query'),
            service('webgriffe.sylius_elasticsearch_plugin.mapper.query_result'),
            service('event_dispatcher'),
            service('webgriffe.sylius_elasticsearch_plugin.helper.sort'),
            service('webgriffe.sylius_elasticsearch_plugin.validator.request'),
        ])
        ->call('setContainer', [service('service_container')])
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber')
    ;
    $services->alias(ProductControllerInterface::class, 'webgriffe.sylius_elasticsearch_plugin.controller.product');
};
