<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use LRuozzi9\SyliusElasticsearchPlugin\Manager\ElasticsearchIndexManager;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('lruozzi9.sylius_elasticsearch_plugin.manager.index', ElasticsearchIndexManager::class)
        ->args([
            service('lruozzi9.sylius_elasticsearch_plugin.client_builder'),
        ])
    ;
};
