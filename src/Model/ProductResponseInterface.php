<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

use Sylius\Component\Core\Model\ProductInterface;

interface ProductResponseInterface extends ResponseInterface, ProductInterface
{
}
