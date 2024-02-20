<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use LRuozzi9\SyliusElasticsearchPlugin\MessageHandler\CreateIndexHandler;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('lruozzi9.sylius_elasticsearch_plugin.message_handler.create_index', CreateIndexHandler::class)
        ->args([
            service('sylius.repository.channel'),
            service('lruozzi9.sylius_elasticsearch_plugin.generator.index_name'),
            service('lruozzi9.sylius_elasticsearch_plugin.client'),
            service('lruozzi9.sylius_elasticsearch_plugin.provider.document_type'),
        ])
        ->tag('messenger.message_handler')
    ;
};
