<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\FilterableInterface;
use Webmozart\Assert\Assert;

final readonly class ProductAttributeContext implements Context
{
    /**
     * @param RepositoryInterface<ProductAttributeInterface> $productAttributeRepository
     */
    public function __construct(
        private RepositoryInterface $productAttributeRepository,
    ) {
    }

    /**
     * @Given /^the product attribute "([^"]+)" is filterable$/
     */
    public function theProductAttributeIsFilterable(string $productAttributeName): void
    {
        $attribute = $this->provideProductAttribute($productAttributeName);
        Assert::isInstanceOf($attribute, FilterableInterface::class);
        $attribute->setFilterable(true);

        $this->productAttributeRepository->add($attribute);
    }

    private function provideProductAttribute(string $name): ProductAttributeInterface
    {
        $code = StringInflector::nameToCode($name);

        $productAttribute = $this->productAttributeRepository->findOneBy(['code' => $code]);
        Assert::notNull($productAttribute);

        return $productAttribute;
    }
}
