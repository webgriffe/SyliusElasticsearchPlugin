<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Generator\IndexNameGenerator;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.generator.index_name', IndexNameGenerator::class)
        ->args([
            '<CHANNEL>_<DOCUMENT_TYPE>_<DATE>_<TIME>',
        ])
    ;
};
