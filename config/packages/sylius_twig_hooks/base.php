<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('sylius_twig_hooks', [
        'hooks' => [
            'sylius_shop.base#javascripts' => [
                'routes' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/shared/layout/base/routes.html.twig',
                    'priority' => 100,
                ],
            ],

            'sylius_shop.base.header.content' => [
                'search' => [
                    'template' => '@WebgriffeSyliusElasticsearchPlugin/shop/shared/layout/base/header/content/search.html.twig',
                    'priority' => 250,
                ],
            ],
        ],
    ]);
};
