<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Serializer\ProductNormalizer;
use Webgriffe\SyliusElasticsearchPlugin\Serializer\ProductTranslationNormalizer;
use Webgriffe\SyliusElasticsearchPlugin\Serializer\ProductVariantNormalizer;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.serializer.product_normalizer', ProductNormalizer::class)
        ->lazy()
        ->args([
            service('sylius.product_variant_resolver.default'),
            service('event_dispatcher'),
            service('serializer'),
            param('kernel.default_locale'),
        ])
        ->tag('serializer.normalizer', ['priority' => 200])
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.serializer.product_variant_normalizer', ProductVariantNormalizer::class)
        ->args([
            service('event_dispatcher'),
        ])
        ->tag('serializer.normalizer', ['priority' => 200])
    ;
};
