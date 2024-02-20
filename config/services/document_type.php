<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use LRuozzi9\SyliusElasticsearchPlugin\DocumentType\ProductDocumentType;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('lruozzi9.sylius_elasticsearch_plugin.document_type.product', ProductDocumentType::class)
        ->args([
            service('sylius.repository.product'),
        ])
        ->tag('lruozzi9.sylius_elasticsearch_plugin.document_type')
    ;
};
