<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

/** @psalm-suppress UndefinedClass */
return static function (FrameworkConfig $framework): void {
    $commandBus = $framework->messenger()->bus('webgriffe_sylius_elasticsearch_plugin.command_bus');

    $commandBus->middleware()
        ->id('validation')
        ->id('doctrine_ping_connection')
        ->id('doctrine_close_connection')
        ->id('doctrine_open_transaction_logger')
        ->id('doctrine_transaction');
};
