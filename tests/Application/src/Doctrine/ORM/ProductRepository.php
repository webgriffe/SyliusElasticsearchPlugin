<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\App\Doctrine\ORM;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository as BaseProductRepository;
use Webgriffe\SyliusElasticsearchPlugin\Doctrine\ORM\ProductDocumentTypeRepositoryTrait;
use Webgriffe\SyliusElasticsearchPlugin\Repository\DocumentTypeRepositoryInterface;

final class ProductRepository extends BaseProductRepository implements DocumentTypeRepositoryInterface
{
    use ProductDocumentTypeRepositoryTrait;
}
