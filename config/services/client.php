<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Client\ElasticsearchClient;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    # Arguments added dynamically by plugin configuration
    $services->set('webgriffe.sylius_elasticsearch_plugin.client', ElasticsearchClient::class)
        ->call('setLogger', [service('logger')->ignoreOnInvalid()])
        ->tag('monolog.logger')
    ;
};
