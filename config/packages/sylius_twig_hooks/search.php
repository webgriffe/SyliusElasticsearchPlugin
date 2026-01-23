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
        ],
    ]);
};
