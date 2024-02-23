<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Twig\PriceExtension;
use Webgriffe\SyliusElasticsearchPlugin\Twig\PriceRuntime;
use Webgriffe\SyliusElasticsearchPlugin\Twig\VariantResolverExtension;
use Webgriffe\SyliusElasticsearchPlugin\Twig\VariantResolverRuntime;
use Webgriffe\SyliusElasticsearchPlugin\Twig\SearchExtension;
use Webgriffe\SyliusElasticsearchPlugin\Twig\SearchRuntime;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.twig.extension.search', SearchExtension::class)
        ->tag('twig.extension')
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.twig.runtime.search', SearchRuntime::class)
        ->args([
            service('form.factory'),
        ])
        ->tag('twig.runtime')
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.twig.extension.variant_resolver', VariantResolverExtension::class)
        ->tag('twig.extension')
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.twig.runtime.variant_resolver', VariantResolverRuntime::class)
        ->args([
            service('sylius.templating.helper.variant_resolver'),
        ])
        ->tag('twig.runtime')
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.twig.extension.price', PriceExtension::class)
        ->tag('twig.extension')
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.twig.runtime.price', PriceRuntime::class)
        ->args([
            service('sylius.templating.helper.price'),
        ])
        ->tag('twig.runtime')
    ;
};
