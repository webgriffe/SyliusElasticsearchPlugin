<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Factory;

use Webgriffe\SyliusElasticsearchPlugin\Model\ProductResponseInterface;

interface ProductResponseFactoryInterface
{
    public function createNew(): ProductResponseInterface;
}
