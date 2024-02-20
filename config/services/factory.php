<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use LRuozzi9\SyliusElasticsearchPlugin\Factory\ProductResponseFactory;
use LRuozzi9\SyliusElasticsearchPlugin\Model\ProductResponse;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('lruozzi9.sylius_elasticsearch_plugin.factory.product_response', ProductResponseFactory::class)
        ->args([
            ProductResponse::class,
        ])
    ;
};
