<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\SyliusUiConfig;

/** @psalm-suppress UndefinedClass */
return static function (SyliusUiConfig $syliusUi): void {
    $syliusUi->event('sylius.shop.layout.header.content', [
        'blocks' => [
            '_search' => '@WebgriffeSyliusElasticsearchPlugin/Layout/Header/_search.html.twig',
        ]
    ]);
};
