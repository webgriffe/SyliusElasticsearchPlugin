<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_shop.base.header.content.form-group' => [
                'form-group' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/shared/layout/base/header/content/search/form-group.html.twig',
                    'priority' => 0,
                ],
            ],
            'sylius_shop.base.header.content.results' => [
                'results' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/shared/layout/base/header/content/search/results.html.twig',
                    'priority' => 0,
                ],
            ],

            'sylius_shop.base.header.form-group' => [
                'form-group' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/shared/layout/base/header/content/search/form-group.html.twig',
                    'priority' => 0,
                ],
            ],
            'sylius_shop.base.header.results' => [
                'results' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/shared/layout/base/header/content/search/results.html.twig',
                    'priority' => 0,
                ],
            ],
        ],
    ]);
};
