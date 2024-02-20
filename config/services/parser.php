<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use LRuozzi9\SyliusElasticsearchPlugin\Parser\ElasticsearchProductDocumentParser;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('lruozzi9.sylius_elasticsearch_plugin.parser.elasticsearch_document', ElasticsearchProductDocumentParser::class)
        ->args([
            service('lruozzi9.sylius_elasticsearch_plugin.factory.product_response'),
            service('sylius.context.locale'),
            service('sylius.context.channel'),
            param('sylius_locale.locale'),
        ])
    ;
};
