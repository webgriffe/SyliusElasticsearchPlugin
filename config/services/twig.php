<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

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
};
