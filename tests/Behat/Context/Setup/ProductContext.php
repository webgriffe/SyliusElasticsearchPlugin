<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Resource\Model\TranslationInterface;
use Webmozart\Assert\Assert;

final readonly class ProductContext implements Context
{
    public function __construct(
        private ObjectManager $objectManager,
    ) {
    }

    /**
     * @Given /^the description of (product "[^"]+") is "([^"]+)" (in the "([^"]+)" locale)$/
     */
    public function descriptionOfProductIsInTheLocale(ProductInterface $product, string $description, string $locale): void
    {
        /** @var ProductTranslationInterface|TranslationInterface $productTranslation */
        $productTranslation = $product->getTranslation($locale);
        Assert::isInstanceOf($productTranslation, ProductTranslationInterface::class);
        $productTranslation->setDescription($description);

        $this->objectManager->flush();
    }

    /**
     * @Given /^the short description of (product "[^"]+") is "([^"]+)" (in the "([^"]+)" locale)$/
     */
    public function shortDescriptionOfProductIsInTheLocale(ProductInterface $product, string $shortDescription, string $locale): void
    {
        /** @var ProductTranslationInterface|TranslationInterface $productTranslation */
        $productTranslation = $product->getTranslation($locale);
        Assert::isInstanceOf($productTranslation, ProductTranslationInterface::class);
        $productTranslation->setShortDescription($shortDescription);

        $this->objectManager->flush();
    }

    /**
     * @Given /^the slug of (product "[^"]+") is "([^"]+)" (in the "([^"]+)" locale)$/
     */
    public function slugOfProductIsInTheLocale(ProductInterface $product, string $slug, string $locale): void
    {
        /** @var ProductTranslationInterface|TranslationInterface $productTranslation */
        $productTranslation = $product->getTranslation($locale);
        Assert::isInstanceOf($productTranslation, ProductTranslationInterface::class);
        $productTranslation->setSlug($slug);

        $this->objectManager->flush();
    }

    /**
     * @Given /^the meta-description of (product "[^"]+") is "([^"]+)" (in the "([^"]+)" locale)$/
     */
    public function metaDescriptionOfProductIsInTheLocale(ProductInterface $product, string $metaDescription, string $locale): void
    {
        /** @var ProductTranslationInterface|TranslationInterface $productTranslation */
        $productTranslation = $product->getTranslation($locale);
        Assert::isInstanceOf($productTranslation, ProductTranslationInterface::class);
        $productTranslation->setMetaDescription($metaDescription);

        $this->objectManager->flush();
    }

    /**
     * @Given /^the meta-keywords of (product "[^"]+") are "([^"]+)" (in the "([^"]+)" locale)$/
     */
    public function metaKeywordsOfProductIsInTheLocale(ProductInterface $product, string $metaKeywords, string $locale): void
    {
        /** @var ProductTranslationInterface|TranslationInterface $productTranslation */
        $productTranslation = $product->getTranslation($locale);
        Assert::isInstanceOf($productTranslation, ProductTranslationInterface::class);
        $productTranslation->setMetaKeywords($metaKeywords);

        $this->objectManager->flush();
    }
}
