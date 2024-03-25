<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Setup\ElasticsearchContext;
use Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Setup\ProductAttributeContext;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.behat.context.setup.elasticsearch', ElasticsearchContext::class)
        ->public()
        ->args([
            service('sylius.repository.channel'),
            service('webgriffe.sylius_elasticsearch_plugin.provider.document_type'),
            service('webgriffe_sylius_elasticsearch_plugin.command_bus'),
        ])
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.behat.context.setup.product_attribute', ProductAttributeContext::class)
        ->public()
        ->args([
            service('sylius.repository.product_attribute'),
        ])
    ;
};
