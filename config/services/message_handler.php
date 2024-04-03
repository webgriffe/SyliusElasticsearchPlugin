<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\MessageHandler\CreateIndexHandler;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.message_handler.create_index', CreateIndexHandler::class)
        ->args([
            service('sylius.repository.channel'),
            service('webgriffe.sylius_elasticsearch_plugin.provider.document_type'),
            service('webgriffe.sylius_elasticsearch_plugin.index_manager.elasticsearch'),
        ])
        ->tag('messenger.message_handler')
    ;
};
