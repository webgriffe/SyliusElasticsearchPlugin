<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('sylius_twig_hooks', [
        'hooks' => [
            'webgriffe_sylius_elasticsearch.search.index' => [
                'content' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.search.index.content' => [
                'breadcrumbs' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content/breadcrumbs.html.twig',
                    'priority' => 100,
                ],
                'body' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content/body.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.search.index.content.body' => [
                'sidebar' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content/body/sidebar.html.twig',
                    'priority' => 0,
                ],
                'main' => [
                    'template' => '@SyliusShop/product/index/content/body/main.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.search.index.content.body.sidebar' => [
                'filters' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content/body/sidebar/filters.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.search.index.content.body.main' => [
                'header' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content/body/main/header.html.twig',
                    'priority' => 300,
                ],
                'filters' => [
                    'template' => '@SyliusShop/product/index/content/body/main/filters.html.twig',
                    'priority' => 200,
                ],
                'results' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content/body/main/results.html.twig',
                    'priority' => 100,
                ],
                'pagination' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content/body/main/pagination.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.search.index.content.body.main.header' => [
                'title' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content/body/main/header/title.html.twig',
                    'priority' => 100,
                ],
                'suggestions' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content/body/main/header/suggestions.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.search.index.content.body.main.filters' => [
                'controls' => [
                    'template' => '@SyliusShop/product/index/content/body/main/filters/controls.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.search.index.content.body.main.filters.controls' => [
                'limit' => [
                    'template' => '@SyliusShop/product/index/content/body/main/filters/controls/limit.html.twig',
                    'priority' => 100,
                ],
                'sort' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content/body/main/filters/controls/sorting.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.search.index.content.body.main.filters.controls.limit' => [
                'toggle' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content/body/main/filters/controls/limit/toggle.html.twig',
                    'priority' => 100,
                ],
                'menu' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/search/index/content/body/main/filters/controls/limit/menu.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.search.index.content.body.main.filters.controls.sorting' => [
                'toggle' => [
                    'template' => '@SyliusShop/product/index/content/body/main/filters/controls/sorting/toggle.html.twig',
                    'priority' => 100,
                ],
                'menu' => [
                    'template' => '@SyliusShop/product/index/content/body/main/filters/controls/sorting/menu.html.twig',
                    'priority' => 0,
                ],
            ],

            'webgriffe_sylius_elasticsearch.search.index.content.body.main.filters.controls.sorting.menu' => [
                'default' => [
                    'template' => '@SyliusShop/product/index/content/body/main/filters/controls/sorting/menu/item.html.twig',
                    'configuration' => [
                        'title' => 'sylius.ui.by_position',
                        'sorting' => '',
                    ],
                    'priority' => 600,
                ],
                'a_to_z' => [
                    'template' => '@SyliusShop/product/index/content/body/main/filters/controls/sorting/menu/item.html.twig',
                    'configuration' => [
                        'title' => 'sylius.ui.from_a_to_z',
                        'sorting' => [
                            'name' => 'sorting',
                            'value' => [
                                'name' => 'asc',
                            ],
                        ],
                    ],
                    'priority' => 500,
                ],
                'z_to_a' => [
                    'template' => '@SyliusShop/product/index/content/body/main/filters/controls/sorting/menu/item.html.twig',
                    'configuration' => [
                        'title' => 'sylius.ui.from_z_to_a',
                        'sorting' => [
                            'name' => 'sorting',
                            'value' => [
                                'name' => 'desc',
                            ],
                        ],
                    ],
                    'priority' => 400,
                ],
                'newest' => [
                    'template' => '@SyliusShop/product/index/content/body/main/filters/controls/sorting/menu/item.html.twig',
                    'configuration' => [
                        'title' => 'sylius.ui.newest_first',
                        'sorting' => [
                            'createdAt' => 'desc',
                        ],
                    ],
                    'priority' => 300,
                ],
                'oldest' => [
                    'template' => '@SyliusShop/product/index/content/body/main/filters/controls/sorting/menu/item.html.twig',
                    'configuration' => [
                        'title' => 'sylius.ui.oldest_first',
                        'sorting' => [
                            'createdAt' => 'asc',
                        ],
                    ],
                    'priority' => 200,
                ],
                'cheapest' => [
                    'template' => '@SyliusShop/product/index/content/body/main/filters/controls/sorting/menu/item.html.twig',
                    'configuration' => [
                        'title' => 'sylius.ui.cheapest_first',
                        'sorting' => [
                            'price' => 'asc',
                        ],
                    ],
                    'priority' => 100,
                ],
                'most_expensive' => [
                    'template' => '@SyliusShop/product/index/content/body/main/filters/controls/sorting/menu/item.html.twig',
                    'configuration' => [
                        'title' => 'sylius.ui.most_expensive_first',
                        'sorting' => [
                            'price' => 'desc',
                        ],
                    ],
                    'priority' => 0,
                ],
            ],
        ],
    ]);
};
