<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Command\IndexCommand;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.command.index', IndexCommand::class)
        ->args([
            service('sylius.repository.channel'),
            service('webgriffe_sylius_elasticsearch_plugin.command_bus'),
            service('webgriffe.sylius_elasticsearch_plugin.provider.document_type'),
            service('webgriffe.sylius_elasticsearch_plugin.index_manager.elasticsearch'),
            'webgriffe:elasticsearch:index',
        ])
        ->tag('console.command')
    ;
};
