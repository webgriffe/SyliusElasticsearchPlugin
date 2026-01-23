<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_admin.product_attribute.create.content.form.general' => [
                'filterable' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/admin/product_attribute/form/general/filterable.html.twig',
                    'priority' => 0,
                ],
            ],
            'sylius_admin.product_attribute.update.content.form.general' => [
                'filterable' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/admin/product_attribute/form/general/filterable.html.twig',
                    'priority' => 0,
                ],
            ],
            'sylius_admin.product_option.create.content.form.sections.general' => [
                'filterable' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/admin/product_option/form/sections/general/filterable.html.twig',
                    'priority' => 0,
                ],
            ],
            'sylius_admin.product_option.update.content.form.sections.general' => [
                'filterable' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/admin/product_option/form/sections/general/filterable.html.twig',
                    'priority' => 0,
                ],
            ],
        ],
    ]);
};
