<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    // Admin
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

    // Shop
    $container->extension('sylius_twig_hooks', [
        'hooks' => [
            'webgriffe_sylius_elasticsearch.product.index' => [
                'content' => [
                    'template' => '@SyliusShop/product/index/content.html.twig',
                    'priority' => 0,
                ],
            ],
            'webgriffe_sylius_elasticsearch.product.index.content' => [
                'breadcrumbs' => [
                    'component' => 'sylius_shop:product:show:breadcrumbs',
                    'props' => [
                        'template' => '@SyliusShop/product/index/content/breadcrumbs.html.twig',
                    ],
                    'priority' => 100,
                ],
                'body' => [
                    'template' => '@SyliusShop/product/index/content/body.html.twig',
                    'priority' => 0,
                ],
            ],
            'webgriffe_sylius_elasticsearch.product.index.content.body' => [
                'sidebar' => [
                    'template' => '@SyliusShop/product/index/content/body/sidebar.html.twig',
                    'priority' => 0,
                ],
                'main' => [
                    'template' => '@SyliusShop/product/index/content/body/main.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.product.index.content.body.sidebar' => [
                'taxonomy' => [
                    'component' => 'sylius_shop:product:show:taxonomy',
                    'props' => [
                        'template' => '@SyliusShop/product/index/content/body/sidebar/taxonomy.html.twig',
                    ],
                    'priority' => 0,
                ],
                'filters' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/product/index/content/body/sidebar/filters.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.product.index.content.body.main' => [
                'header' => [
                    'component' => 'sylius_shop:product:show:header',
                    'props' => [
                        'template' => '@SyliusShop/product/index/content/body/main/header.html.twig',
                    ],
                    'priority' => 300,
                ],
                'products' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/product/index/content/body/main/products.html.twig',
                    'priority' => 100,
                ],
                'pagination' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/product/index/content/body/main/pagination.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.product.index.content.body.main.header' => [
                'image' => [
                    'template' => '@SyliusShop/product/index/content/body/main/header/image.html.twig',
                    'priority' => 300,
                ],
                'name' => [
                    'template' => '@SyliusShop/product/index/content/body/main/header/name.html.twig',
                    'priority' => 100,
                ],
                'description' => [
                    'template' => '@SyliusShop/product/index/content/body/main/header/description.html.twig',
                    'priority' => 0,
                ],
            ],
        ],
    ]);
};
