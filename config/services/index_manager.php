<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\IndexManager\ElasticsearchIndexManager;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.index_manager.elasticsearch', ElasticsearchIndexManager::class)
        ->args([
            service('webgriffe.sylius_elasticsearch_plugin.client'),
            service('webgriffe.sylius_elasticsearch_plugin.generator.index_name'),
            service('lock.factory'),
        ])
    ;
};
