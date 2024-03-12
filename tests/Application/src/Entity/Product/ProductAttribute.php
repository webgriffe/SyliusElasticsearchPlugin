<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductAttribute as BaseProductAttribute;
use Webgriffe\SyliusElasticsearchPlugin\Doctrine\ORM\FilterableTrait;
use Webgriffe\SyliusElasticsearchPlugin\Model\FilterableInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_attribute")
 */
class ProductAttribute extends BaseProductAttribute implements FilterableInterface
{
    use FilterableTrait;
}
