<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Persistence\ObjectManager;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Attribute\Factory\AttributeFactoryInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductAttributeTranslationInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\FilterableInterface;
use Webmozart\Assert\Assert;

final readonly class ProductAttributeContext implements Context
{
    /**
     * @param RepositoryInterface<ProductAttributeInterface> $productAttributeRepository
     * @param FactoryInterface<ProductAttributeValueInterface> $productAttributeValueFactory
     * @param FactoryInterface<ProductAttributeTranslationInterface> $productAttributeTranslationFactory
     */
    public function __construct(
        private RepositoryInterface $productAttributeRepository,
        private ObjectManager $objectManager,
        private FactoryInterface $productAttributeValueFactory,
        private FactoryInterface $productAttributeTranslationFactory,
        private AttributeFactoryInterface $productAttributeFactory,
        private SharedStorageInterface $sharedStorage,
    ) {
    }

    /**
     * @Given /^the store has(?:| also)(?:| a| an) (checkbox|date|datetime) product attribute "([^"]+)"$/
     */
    public function theStoreHasACheckboxProductAttribute(string $type, string $name): void
    {
        $productAttribute = $this->createProductAttribute($type, $name);

        $this->saveProductAttribute($productAttribute);
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

    /**
     * @Given /^(this product) has an integer attribute "([^"]+)" with value "([^"]+)"$/
     * @Given /^(this product) has an integer attribute "([^"]+)" with value "([^"]+)" in ("[^"]+" locale)$/
     */
    public function thisProductHasAnIntegerAttributeWithValue(
        ProductInterface $product,
        string $productAttributeName,
        string $value,
        string $language = 'en_US',
    ): void {
        $attribute = $this->provideProductAttribute($productAttributeName);
        $attributeValue = $this->createProductAttributeValue((int) $value, $attribute, $language);
        $product->addAttribute($attributeValue);

        $this->objectManager->flush();
    }

    /**
     * @Given /^(this product) has a percent attribute "([^"]+)" with value "([^"]+)"$/
     */
    public function thisProductHasAPercentAttributeWithValue(
        ProductInterface $product,
        string $productAttributeName,
        string $value,
        string $language = 'en_US',
    ): void {
        $attribute = $this->provideProductAttribute($productAttributeName);
        $attributeValue = $this->createProductAttributeValue((float) $value, $attribute, $language);
        $product->addAttribute($attributeValue);

        $this->objectManager->flush();
    }

    /**
     * @Given /^(this product attribute) has translation "([^"]+)" in ("[^"]+" locale)$/
     */
    public function thisProductAttributeHasTranslationInLocale(
        ProductAttributeInterface $productAttribute,
        string $translation,
        string $language = 'en_US',
    ): void {
        $productTranslation = $this->productAttributeTranslationFactory->createNew();
        $productTranslation->setLocale($language);
        $productTranslation->setTranslatable($productAttribute);
        $productTranslation->setName($translation);
        $productAttribute->addTranslation($productTranslation);

        $this->objectManager->flush();
    }

    private function provideProductAttribute(string $name): ProductAttributeInterface
    {
        $code = StringInflector::nameToCode($name);

        $productAttribute = $this->productAttributeRepository->findOneBy(['code' => $code]);
        Assert::notNull($productAttribute);

        return $productAttribute;
    }

    private function createProductAttributeValue(
        int|float $value,
        ProductAttributeInterface $attribute,
        ?string $localeCode = 'en_US',
        bool $translatable = true,
    ): ProductAttributeValueInterface {
        $attribute->setTranslatable($translatable);
        $this->objectManager->persist($attribute);

        /** @var ProductAttributeValueInterface $attributeValue */
        $attributeValue = $this->productAttributeValueFactory->createNew();
        $attributeValue->setAttribute($attribute);
        $attributeValue->setValue($value);
        $attributeValue->setLocaleCode($localeCode);

        $this->objectManager->persist($attributeValue);

        return $attributeValue;
    }

    private function createProductAttribute(
        string $type,
        string $name,
        ?string $code = null,
        bool $translatable = true,
    ): ProductAttributeInterface {
        /** @var ProductAttributeInterface $productAttribute */
        $productAttribute = $this->productAttributeFactory->createTyped($type);

        $code = $code ?? StringInflector::nameToCode($name);

        $productAttribute->setCode($code);
        $productAttribute->setTranslatable($translatable);
        $productAttribute->setName($name);

        return $productAttribute;
    }

    private function saveProductAttribute(ProductAttributeInterface $productAttribute): void
    {
        $this->productAttributeRepository->add($productAttribute);
        $this->sharedStorage->set('product_attribute', $productAttribute);
    }
}
