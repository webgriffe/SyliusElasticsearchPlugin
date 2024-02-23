<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Client\ElasticsearchClient;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.client', ElasticsearchClient::class)
        ->args([
            '127.0.0.1',
            '9200',
        ])
        ->tag('monolog.logger')
    ;
};
