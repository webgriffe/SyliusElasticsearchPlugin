<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\DocumentType\ProductDocumentType;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.document_type.product', ProductDocumentType::class)
        ->args([
            service('sylius.repository.product'),
            service('serializer'),
            service('sylius.repository.locale'),
        ])
        ->tag('webgriffe.sylius_elasticsearch_plugin.document_type')
    ;
};
