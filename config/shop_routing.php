<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('sylius_shop_product_index', '/taxons/{slug}')
        ->controller(['webgriffe.sylius_elasticsearch_plugin.controller.product', '__invoke'])
        ->methods(['GET'])
        ->requirements(['slug' => '.+(?<!/)'])
    ;

    $routes->add('sylius_shop_search', '/search/{query}')
        ->controller(['webgriffe.sylius_elasticsearch_plugin.controller.search', '__invoke'])
        ->methods(['GET', 'POST'])
        ->defaults(['query' => null])
        ->requirements(['query' => '.+(?<!/)'])
    ;

    $routes->add('sylius_shop_instant_search', '/instant-search/{query}')
        ->controller(['webgriffe.sylius_elasticsearch_plugin.controller.instant_search', '__invoke'])
        ->methods(['GET'])
        ->requirements(['query' => '.+(?<!/)'])
    ;
};
