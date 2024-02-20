<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Factory;

use LRuozzi9\SyliusElasticsearchPlugin\Model\ProductResponseInterface;

interface ProductResponseFactoryInterface
{
    public function createNew(): ProductResponseInterface;
}
