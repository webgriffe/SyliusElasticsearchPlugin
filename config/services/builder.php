<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Builder\TwigQueryBuilder;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.builder.query', TwigQueryBuilder::class)
        ->args([
            service('twig'),
            service('sylius.context.locale'),
            service('monolog.logger.webgriffe_sylius_elasticsearch_plugin'),
            service('sylius.repository.product_attribute'),
            service('sylius.repository.product_option'),
        ])
    ;
};
