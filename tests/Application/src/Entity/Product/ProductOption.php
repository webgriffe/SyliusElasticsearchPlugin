<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductOption as BaseProductOption;
use Webgriffe\SyliusElasticsearchPlugin\Model\DoctrineORMFilterableTrait;
use Webgriffe\SyliusElasticsearchPlugin\Model\FilterableInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_option")
 */
class ProductOption extends BaseProductOption implements FilterableInterface
{
    use DoctrineORMFilterableTrait;
}
