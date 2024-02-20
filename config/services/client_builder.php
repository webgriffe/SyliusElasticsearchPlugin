<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use LRuozzi9\SyliusElasticsearchPlugin\ClientBuilder\ElasticsearchClientBuilder;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('lruozzi9.sylius_elasticsearch_plugin.client_builder', ElasticsearchClientBuilder::class)
        ->args([
            '127.0.0.1',
            '9200',
            service('logger'),
        ])
    ;
};
