<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use LRuozzi9\SyliusElasticsearchPlugin\Generator\IndexNameGenerator;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('lruozzi9.sylius_elasticsearch_plugin.generator.index_name', IndexNameGenerator::class)
        ->args([
            '<CHANNEL>_<DATE>_<TIME>',
        ])
    ;
};
