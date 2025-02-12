<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Resolver\RequestTaxonResolver;
use Webgriffe\SyliusElasticsearchPlugin\Resolver\RequestTaxonResolverInterface;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.resolver.request_taxon', RequestTaxonResolver::class)
        ->args([
            service('sylius.repository.taxon'),
            service('sylius.context.locale'),
        ])
    ;

    $services->alias(RequestTaxonResolverInterface::class, 'webgriffe.sylius_elasticsearch_plugin.resolver.request_taxon');
};
