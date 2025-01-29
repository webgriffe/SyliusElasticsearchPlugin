<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Parser;

if (!interface_exists(\Sylius\Resource\Factory\FactoryInterface::class)) {
    class_alias(\Sylius\Component\Resource\Factory\FactoryInterface::class, \Sylius\Resource\Factory\FactoryInterface::class);
}
use DateTime;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\CatalogPromotionInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser\ProductAttributeDocumentParserEvent;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser\ProductAttributeValueDocumentParserEvent;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser\ProductDocumentParserEvent;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser\ProductImageDocumentParserEvent;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser\ProductOptionDocumentParserEvent;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser\ProductOptionValueDocumentParserEvent;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser\ProductVariantDocumentParserEvent;
use Webgriffe\SyliusElasticsearchPlugin\Factory\ProductResponseFactoryInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\ProductResponseInterface;
use Webmozart\Assert\Assert;

/**
 * @psalm-type LocalizedField = array<array-key, array<string, string>>
 */
final class ElasticsearchProductDocumentParser implements DocumentParserInterface
{
    private ?string $defaultLocaleCode = null;

    /** @var array<string|int, ProductVariantInterface> */
    private array $productVariants = [];

    /** @var array<string|int, ProductOptionValueInterface> */
    private array $productOptionValues = [];

    /**
     * @param FactoryInterface<ProductImageInterface> $productImageFactory
     * @param ProductVariantFactoryInterface<ProductVariantInterface> $productVariantFactory
     * @param FactoryInterface<ChannelPricingInterface> $channelPricingFactory
     * @param FactoryInterface<CatalogPromotionInterface> $catalogPromotionFactory
     * @param FactoryInterface<ProductOptionInterface> $productOptionFactory
     * @param FactoryInterface<ProductOptionValueInterface> $productOptionValueFactory
     * @param FactoryInterface<ProductAttributeInterface> $productAttributeFactory
     * @param FactoryInterface<ProductAttributeValueInterface> $productAttributeValueFactory
     */
    public function __construct(
        private readonly ProductResponseFactoryInterface $productResponseFactory,
        private readonly LocaleContextInterface $localeContext,
        private readonly ChannelContextInterface $channelContext,
        private readonly FactoryInterface $productImageFactory,
        private readonly ProductVariantFactoryInterface $productVariantFactory,
        private readonly FactoryInterface $channelPricingFactory,
        private readonly FactoryInterface $catalogPromotionFactory,
        private readonly FactoryInterface $productOptionFactory,
        private readonly FactoryInterface $productOptionValueFactory,
        private readonly FactoryInterface $productAttributeFactory,
        private readonly FactoryInterface $productAttributeValueFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly string $fallbackLocaleCode,
    ) {
    }

    public function parse(array $document): ProductResponseInterface
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();
        $defaultLocale = $channel->getDefaultLocale();
        $this->defaultLocaleCode = $this->fallbackLocaleCode;
        if ($defaultLocale instanceof LocaleInterface) {
            $defaultLocaleCode = $defaultLocale->getCode();
            if ($defaultLocaleCode !== null) {
                $this->defaultLocaleCode = $defaultLocaleCode;
            }
        }
        /** @var array{sylius-id: int, code: string, name: LocalizedField, description: LocalizedField, short-description: LocalizedField, taxons: array, main_taxon: array, slug: LocalizedField, images: array, variants: array, product-options: array, translated-attributes: array, attributes: array} $source */
        $source = $document['_source'];
        $localeCode = $this->localeContext->getLocaleCode();
        $productResponse = $this->productResponseFactory->createNew();
        $productResponse->setCode($source['code']);
        $productResponse->setCurrentLocale($localeCode);
        $productResponse->setName($this->getValueFromLocalizedField($source['name'], $localeCode));
        $productResponse->setSlug($this->getSlug($source['slug'], $localeCode));
        $productResponse->setDescription($this->getValueFromLocalizedField($source['description'], $localeCode));
        $productResponse->setShortDescription($this->getValueFromLocalizedField($source['short-description'], $localeCode));

        $this->productOptionValues = [];
        /** @var array{sylius-id: int|string, code: string, name: array<array-key, array<string, string>>, position: int, filterable: bool, values: array} $esProductOption */
        foreach ($source['product-options'] as $esProductOption) {
            $productOption = $this->productOptionFactory->createNew();
            $productOption->setCode($esProductOption['code']);
            $productOption->setPosition($esProductOption['position']);
            $productOption->setCurrentLocale($localeCode);
            $productOption->setName($this->getValueFromLocalizedField($esProductOption['name'], $localeCode));

            /** @var array{sylius-id: int|string, code: string, value: string, name: array<array-key, array<string, string>>} $esProductOptionValue */
            foreach ($esProductOption['values'] as $esProductOptionValue) {
                $productOptionValue = $this->productOptionValueFactory->createNew();
                $productOptionValue->setCode($esProductOptionValue['code']);
                $productOptionValue->setOption($productOption);
                $productOptionValue->setCurrentLocale($localeCode);
                $productOptionValue->setFallbackLocale($this->fallbackLocaleCode);
                $productOptionValue->setValue($esProductOptionValue['value']);
                $this->productOptionValues[$esProductOptionValue['sylius-id']] = $productOptionValue;
                $event = new ProductOptionValueDocumentParserEvent($esProductOptionValue, $productOptionValue, $productOption, $productResponse);
                $this->eventDispatcher->dispatch($event);

                $productOption->addValue($productOptionValue);
            }
            $event = new ProductOptionDocumentParserEvent($esProductOption, $productOption, $productResponse);
            $this->eventDispatcher->dispatch($event);

            $productResponse->addOption($productOption);
        }

        /** @var array{sylius-id: int|string, code: string, type: string, storage-type: string, position: int, translatable: bool, filterable: bool, name: array<array-key, array<string, string>>, values: array} $esTranslatedAttribute */
        foreach ($source['translated-attributes'] as $esTranslatedAttribute) {
            $productAttribute = $this->productAttributeFactory->createNew();
            $productAttribute->setCode($esTranslatedAttribute['code']);
            $productAttribute->setStorageType($esTranslatedAttribute['storage-type']);
            $productAttribute->setType($esTranslatedAttribute['type']);
            $productAttribute->setTranslatable(true);
            $productAttribute->setPosition($esTranslatedAttribute['position']);
            $productAttribute->setCurrentLocale($localeCode);
            $productAttribute->setName($this->getValueFromLocalizedField($esTranslatedAttribute['name'], $localeCode));
            $event = new ProductAttributeDocumentParserEvent($esTranslatedAttribute, $productAttribute, $productResponse);
            $this->eventDispatcher->dispatch($event);

            if (!array_key_exists($this->defaultLocaleCode, $esTranslatedAttribute['values'])) {
                /** @var array<array-key, array{sylius-id: int|string, code: string, locale: string, values: array<array-key, string>}> $attributeValues */
                $attributeValues = $esTranslatedAttribute['values'][$this->fallbackLocaleCode];
                $usedLocale = $this->fallbackLocaleCode;
            } else {
                /** @var array<array-key, array{sylius-id: int|string, code: string, locale: string, values: array<array-key, string>}> $attributeValues */
                $attributeValues = $esTranslatedAttribute['values'][$this->defaultLocaleCode];
                $usedLocale = $this->defaultLocaleCode;
            }
            if (array_key_exists($localeCode, $esTranslatedAttribute['values'])) {
                $usedLocale = $localeCode;
                /** @var array<array-key, array{sylius-id: int|string, code: string, locale: string, values: array<array-key, string>}> $attributeValues */
                $attributeValues = $esTranslatedAttribute['values'][$localeCode];
            }

            foreach ($attributeValues as $esProductAttributeValue) {
                $productAttributeValue = $this->productAttributeValueFactory->createNew();
                $productAttributeValue->setAttribute($productAttribute);
                $productAttributeValue->setLocaleCode($usedLocale);
                $productAttributeValue->setSubject($productResponse);
                $productAttributeValue->setValue($this->getAttributeValueByStorageType($esProductAttributeValue['values'], $esTranslatedAttribute['storage-type']));
                $event = new ProductAttributeValueDocumentParserEvent($esProductAttributeValue, $productAttributeValue, $productAttribute, $productResponse);
                $this->eventDispatcher->dispatch($event);

                $productResponse->addAttribute($productAttributeValue);
            }
        }

        /** @var array{sylius-id: int|string, code: string, type: string, storage-type: string, position: int, translatable: bool, filterable: bool, name: array<array-key, array<string, string>>, values: array} $esTranslatedAttribute */
        foreach ($source['attributes'] as $esTranslatedAttribute) {
            $productAttribute = $this->productAttributeFactory->createNew();
            $productAttribute->setCode($esTranslatedAttribute['code']);
            $productAttribute->setStorageType($esTranslatedAttribute['storage-type']);
            $productAttribute->setType($esTranslatedAttribute['type']);
            $productAttribute->setTranslatable(false);
            $productAttribute->setPosition($esTranslatedAttribute['position']);
            $productAttribute->setCurrentLocale($localeCode);
            $productAttribute->setName($this->getValueFromLocalizedField($esTranslatedAttribute['name'], $localeCode));
            $event = new ProductAttributeDocumentParserEvent($esTranslatedAttribute, $productAttribute, $productResponse);
            $this->eventDispatcher->dispatch($event);

            /** @var array{sylius-id: int|string, code: string, locale: string, values: array<array-key, string>} $esProductAttributeValue */
            foreach ($esTranslatedAttribute['values'] as $esProductAttributeValue) {
                $productAttributeValue = $this->productAttributeValueFactory->createNew();
                $productAttributeValue->setAttribute($productAttribute);
                $productAttributeValue->setLocaleCode($localeCode);
                $productAttributeValue->setSubject($productResponse);
                $firstValue = reset($esProductAttributeValue['values']);
                if ($productAttribute->getStorageType() === AttributeValueInterface::STORAGE_DATETIME ||
                    $productAttribute->getStorageType() === AttributeValueInterface::STORAGE_DATE
                ) {
                    $firstValue = new DateTime((string) $firstValue);
                } elseif ($productAttribute->getStorageType() === AttributeValueInterface::STORAGE_JSON) {
                    $firstValue = [$firstValue];
                }
                $productAttributeValue->setValue($firstValue);
                $event = new ProductAttributeValueDocumentParserEvent($esProductAttributeValue, $productAttributeValue, $productAttribute, $productResponse);
                $this->eventDispatcher->dispatch($event);

                $productResponse->addAttribute($productAttributeValue);
            }
        }

        // Doing this sorting here could avoid to make any DB query to get the variants in the right order just for
        // getting the "default variant"
        /** @var array<array-key, array{sylius-id: int|string, code: ?string, enabled: ?bool, position: int, price: array{price: ?int, original-price: ?int, applied-promotions: array}, options: array, on-hand: ?int, on-hold: ?int, is-tracked: bool}> $sortedVariants */
        $sortedVariants = $source['variants'];
        usort(
            $sortedVariants,
            static function (array $a, array $b): int {
                return $a['position'] <=> $b['position'];
            },
        );
        $this->productVariants = [];
        foreach ($sortedVariants as $esVariant) {
            $productVariant = $this->productVariantFactory->createForProduct($productResponse);
            Assert::isInstanceOf($productVariant, ProductVariantInterface::class);
            $productVariant->setCode($esVariant['code']);
            $productVariant->setEnabled($esVariant['enabled']);
            $productVariant->setPosition($esVariant['position']);
            $productVariant->setOnHand($esVariant['on-hand']);
            $productVariant->setOnHold($esVariant['on-hold']);
            $productVariant->setTracked($esVariant['is-tracked']);
            $this->productVariants[$esVariant['sylius-id']] = $productVariant;

            $channelPricing = $this->channelPricingFactory->createNew();
            $channelPricing->setPrice($esVariant['price']['price']);
            $channelPricing->setOriginalPrice($esVariant['price']['original-price']);
            $channelPricing->setChannelCode($channel->getCode());
            /** @var array{label: LocalizedField} $esAppliedPromotion */
            foreach ($esVariant['price']['applied-promotions'] as $esAppliedPromotion) {
                $catalogPromotion = $this->catalogPromotionFactory->createNew();
                $catalogPromotion->setCurrentLocale($localeCode);
                $catalogPromotion->setLabel($this->getValueFromLocalizedField($esAppliedPromotion['label'], $localeCode));

                $channelPricing->addAppliedPromotion($catalogPromotion);
            }
            $productVariant->addChannelPricing($channelPricing);

            /** @var array{sylius-id: int|string, code: string, name: array, filterable: bool, value: array{sylius-id: int|string, code: string, value: string, name: array}} $esOption */
            foreach ($esVariant['options'] as $esOption) {
                if (!array_key_exists($esOption['value']['sylius-id'], $this->productOptionValues)) {
                    continue;
                }
                $productVariant->addOptionValue($this->productOptionValues[$esOption['value']['sylius-id']]);
            }
            $event = new ProductVariantDocumentParserEvent($esVariant, $productVariant, $productResponse);
            $this->eventDispatcher->dispatch($event);

            $productResponse->addVariant($productVariant);
        }

        /** @var array{path: ?string, type: ?string, variants: array} $esImage */
        foreach ($source['images'] as $esImage) {
            $productImage = $this->productImageFactory->createNew();
            $productImage->setPath($esImage['path']);
            $productImage->setType($esImage['type']);
            /** @var array{sylius-id: int|string, code: string} $esVariantImage */
            foreach ($esImage['variants'] as $esVariantImage) {
                if (!array_key_exists($esVariantImage['sylius-id'], $this->productVariants)) {
                    continue;
                }
                $productVariant = $this->productVariants[$esVariantImage['sylius-id']];
                $productImage->addProductVariant($productVariant);
            }
            $event = new ProductImageDocumentParserEvent($esImage, $productImage, $productResponse);
            $this->eventDispatcher->dispatch($event);

            $productResponse->addImage($productImage);
        }

        $event = new ProductDocumentParserEvent($source, $productResponse);
        $this->eventDispatcher->dispatch($event);

        return $productResponse;
    }

    /**
     * @param LocalizedField $localizedField
     */
    private function getValueFromLocalizedField(array $localizedField, string $localeCode): ?string
    {
        $fallbackValue = null;
        $defaultLocale = $this->defaultLocaleCode;
        Assert::string($defaultLocale);
        foreach ($localizedField as $field) {
            if (array_key_exists($localeCode, $field)) {
                return $field[$localeCode];
            }
            if (array_key_exists($defaultLocale, $field)) {
                $fallbackValue = $field[$defaultLocale];
            }
        }

        return $fallbackValue;
    }

    /**
     * @param LocalizedField $localizedSlug
     */
    private function getSlug(array $localizedSlug, string $localeCode): string
    {
        foreach ($localizedSlug as $slug) {
            if (array_key_exists($localeCode, $slug)) {
                return $slug[$localeCode];
            }
        }

        throw new RuntimeException('Slug not found');
    }

    /**
     * @param array<array-key, string|bool|int|float> $values
     */
    private function getAttributeValueByStorageType(array $values, string $storageType): array|string|bool|int|float|DateTime
    {
        if ($storageType === AttributeValueInterface::STORAGE_JSON) {
            return $values;
        }
        $firstValue = reset($values);
        if ($storageType === AttributeValueInterface::STORAGE_BOOLEAN) {
            return $firstValue === true;
        }
        if ($storageType === AttributeValueInterface::STORAGE_DATETIME || $storageType === AttributeValueInterface::STORAGE_DATE) {
            return new DateTime((string) $firstValue);
        }

        return $firstValue;
    }
}
