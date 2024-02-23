<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Factory\ProductResponseFactory;
use Webgriffe\SyliusElasticsearchPlugin\Model\ProductResponse;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.factory.product_response', ProductResponseFactory::class)
        ->args([
            ProductResponse::class,
        ])
    ;
};
