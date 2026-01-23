<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\InMemory\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Product\Repository\ProductVariantRepositoryInterface;
use Sylius\Resource\Doctrine\Persistence\InMemoryRepository;

final class ProductVariantRepository extends InMemoryRepository implements ProductVariantRepositoryInterface
{
    public function createQueryBuilderByProductId(string $locale, mixed $productId): QueryBuilder
    {
        // TODO: Implement createQueryBuilderByProductId() method.
    }

    public function createQueryBuilderByProductCode(string $locale, string $productCode): QueryBuilder
    {
        // TODO: Implement createQueryBuilderByProductCode() method.
    }

    public function findByName(string $name, string $locale): array
    {
        // TODO: Implement findByName() method.
    }

    public function findByNameAndProduct(string $name, string $locale, ProductInterface $product): array
    {
        // TODO: Implement findByNameAndProduct() method.
    }

    public function findOneByCodeAndProductCode(string $code, string $productCode): ?ProductVariantInterface
    {
        // TODO: Implement findOneByCodeAndProductCode() method.
    }

    public function findByCodesAndProductCode(array $codes, string $productCode): array
    {
        // TODO: Implement findByCodesAndProductCode() method.
    }

    public function findByCodes(array $codes): array
    {
        // TODO: Implement findByCodes() method.
    }

    public function findOneByIdAndProductId(mixed $id, mixed $productId): ?ProductVariantInterface
    {
        // TODO: Implement findOneByIdAndProductId() method.
    }

    public function findByPhraseAndProductCode(string $phrase, string $locale, string $productCode): array
    {
        // TODO: Implement findByPhraseAndProductCode() method.
    }

    public function findByPhrase(string $phrase, string $locale, ?int $limit = null): array
    {
        // TODO: Implement findByPhrase() method.
    }

    public function getCodesOfAllVariants(): array
    {
        // TODO: Implement getCodesOfAllVariants() method.
    }
}
