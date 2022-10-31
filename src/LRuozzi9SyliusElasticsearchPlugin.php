<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use function dirname;

final class LRuozzi9SyliusElasticsearchPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
