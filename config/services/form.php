<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Webgriffe\SyliusElasticsearchPlugin\Form\Extension\ProductAttributeTypeExtension;
use Webgriffe\SyliusElasticsearchPlugin\Form\Extension\ProductOptionTypeExtension;
use Webgriffe\SyliusElasticsearchPlugin\Form\Type\SearchType;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $services->set('webgriffe.sylius_elasticsearch_plugin.form.type.search', SearchType::class)
        ->args([
            service('router'),
        ])
        ->tag('form.type')
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.form.type_extension.product_attribute', ProductAttributeTypeExtension::class)
        ->tag('form.type_extension')
    ;

    $services->set('webgriffe.sylius_elasticsearch_plugin.form.type_extension.product_option', ProductOptionTypeExtension::class)
        ->tag('form.type_extension')
    ;
};
