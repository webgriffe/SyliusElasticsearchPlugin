<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Serializer\ProductNormalizer;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.serializer.product_normalizer', ProductNormalizer::class)
        ->tag('serializer.normalizer', ['priority' => 200])
    ;
};
