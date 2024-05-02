<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\MessageHandler\CreateIndexHandler;
use Webgriffe\SyliusElasticsearchPlugin\MessageHandler\RemoveDocumentIfExistsHandler;
use Webgriffe\SyliusElasticsearchPlugin\MessageHandler\UpsertDocumentHandler;

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

    $services->set('webgriffe.sylius_elasticsearch_plugin.message_handler.upsert_document', UpsertDocumentHandler::class)
        ->args([
            service('sylius.repository.channel'),
            service('webgriffe.sylius_elasticsearch_plugin.provider.document_type'),
            service('webgriffe.sylius_elasticsearch_plugin.index_manager.elasticsearch'),
        ])
        ->tag('messenger.message_handler')
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.message_handler.remove_document_if_exists', RemoveDocumentIfExistsHandler::class)
        ->args([
            service('sylius.repository.channel'),
            service('webgriffe.sylius_elasticsearch_plugin.provider.document_type'),
            service('webgriffe.sylius_elasticsearch_plugin.index_manager.elasticsearch'),
        ])
        ->tag('messenger.message_handler')
    ;
};
