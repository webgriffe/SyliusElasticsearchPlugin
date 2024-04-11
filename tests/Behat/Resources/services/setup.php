<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Setup\ElasticsearchContext;
use Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Setup\ProductAttributeContext;
use Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Setup\ProductContext;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.behat.context.setup.elasticsearch', ElasticsearchContext::class)
        ->public()
        ->args([
            service('sylius.repository.channel'),
            service('webgriffe.sylius_elasticsearch_plugin.provider.document_type'),
            service('webgriffe.sylius_elasticsearch_plugin.index_manager.elasticsearch'),
        ])
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.behat.context.setup.product_attribute', ProductAttributeContext::class)
        ->public()
        ->args([
            service('sylius.repository.product_attribute'),
            service('doctrine.orm.entity_manager'),
            service('sylius.factory.product_attribute_value'),
            service('sylius.factory.product_attribute_translation'),
            service('sylius.factory.product_attribute'),
            service('sylius.behat.shared_storage'),
        ])
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.behat.context.setup.product', ProductContext::class)
        ->public()
        ->args([
            service('doctrine.orm.entity_manager'),
        ])
    ;
};
