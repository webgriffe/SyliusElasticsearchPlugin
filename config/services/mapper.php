<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Mapper\QueryResultMapper;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.mapper.query_result', QueryResultMapper::class)
        ->args([
            service('webgriffe.sylius_elasticsearch_plugin.parser.elasticsearch_document'),
        ])
    ;
};
