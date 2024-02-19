<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use LRuozzi9\SyliusElasticsearchPlugin\Command\IndexCommand;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('lruozzi9.sylius_elasticsearch_plugin.command.index', IndexCommand::class)
        ->args([
            service('sylius.repository.channel'),
            service('lruozzi9_sylius_elasticsearch_plugin.command_bus'),
            service('lruozzi9.sylius_elasticsearch_plugin.provider.document_type'),
            'lruozzi9.elasticsearch.index',
        ])
        ->tag('console.command')
    ;
};
