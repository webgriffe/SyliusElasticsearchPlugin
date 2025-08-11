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
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
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
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser\ProductTaxonDocumentParserEvent;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser\ProductVariantDocumentParserEvent;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser\TaxonDocumentParserEvent;
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

    /** @var array<string|int, TaxonInterface> */
    private array $taxons = [];

    /**
     * @param FactoryInterface<ProductImageInterface> $productImageFactory
     * @param ProductVariantFactoryInterface<ProductVariantInterface> $productVariantFactory
     * @param FactoryInterface<ChannelPricingInterface> $channelPricingFactory
     * @param FactoryInterface<CatalogPromotionInterface> $catalogPromotionFactory
     * @param FactoryInterface<ProductOptionInterface> $productOptionFactory
     * @param FactoryInterface<ProductOptionValueInterface> $productOptionValueFactory
     * @param FactoryInterface<ProductAttributeInterface> $productAttributeFactory
     * @param FactoryInterface<ProductAttributeValueInterface> $productAttributeValueFactory
     * @param FactoryInterface<ProductTaxonInterface> $productTaxonFactory
     * @param FactoryInterface<TaxonInterface> $taxonFactory
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
        private readonly FactoryInterface $productTaxonFactory,
        private readonly FactoryInterface $taxonFactory,
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
        /** @var array{sylius-id: int, code: string, name: LocalizedField, description: LocalizedField, short-description: LocalizedField, product-taxons: array, main-taxon: array, slug: LocalizedField, images: array, variants: array, product-options: array, attributes: array} $source */
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

        /** @var array{sylius-id: int|string, code: string, type: string, storage-type: string, position: int, translatable: bool, filterable: bool, name: array<array-key, array<string, string>>, values: array} $esAttribute */
        foreach ($source['attributes'] as $esAttribute) {
            // Check if it exists at least one locale available for the current request
            if (!array_key_exists($localeCode, $esAttribute['values']) &&
                !array_key_exists($this->defaultLocaleCode, $esAttribute['values']) &&
                !array_key_exists($this->fallbackLocaleCode, $esAttribute['values'])
            ) {
                continue;
            }
            $productAttribute = $this->productAttributeFactory->createNew();
            $productAttribute->setCode($esAttribute['code']);
            $productAttribute->setStorageType($esAttribute['storage-type']);
            $productAttribute->setType($esAttribute['type']);
            $productAttribute->setTranslatable(true);
            $productAttribute->setPosition($esAttribute['position']);
            $productAttribute->setCurrentLocale($localeCode);
            $productAttribute->setName($this->getValueFromLocalizedField($esAttribute['name'], $localeCode));
            $event = new ProductAttributeDocumentParserEvent($esAttribute, $productAttribute, $productResponse);
            $this->eventDispatcher->dispatch($event);

            $usedLocale = $this->fallbackLocaleCode;
            if (array_key_exists($this->defaultLocaleCode, $esAttribute['values'])) {
                $usedLocale = $this->defaultLocaleCode;
            }
            if (array_key_exists($localeCode, $esAttribute['values'])) {
                $usedLocale = $localeCode;
            }
            /** @var array<array-key, array{sylius-id: int|string, code: string, locale: string, values: array<array-key, string>}> $attributeValues */
            $attributeValues = $esAttribute['values'][$usedLocale];

            foreach ($attributeValues as $esProductAttributeValue) {
                $productAttributeValue = $this->productAttributeValueFactory->createNew();
                $productAttributeValue->setAttribute($productAttribute);
                $productAttributeValue->setLocaleCode($usedLocale);
                $productAttributeValue->setSubject($productResponse);
                $productAttributeValue->setValue($this->getAttributeValueByStorageType($esProductAttributeValue['values'], $esAttribute['storage-type']));
                $event = new ProductAttributeValueDocumentParserEvent($esProductAttributeValue, $productAttributeValue, $productAttribute, $productResponse);
                $this->eventDispatcher->dispatch($event);

                $productResponse->addAttribute($productAttributeValue);
            }
        }

        // Doing this sorting here could avoid to make any DB query to get the variants in the right order just for
        // getting the "default variant"
        /** @var array<array-key, array{sylius-id: int|string, code: ?string, enabled: ?bool, position: int, price: ?array{price: ?int, original-price: ?int, applied-promotions: array}, options: array, on-hand: ?int, on-hold: ?int, is-tracked: bool}> $sortedVariants */
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

            if ($esVariant['price'] !== null) {
                $channelPricing = $this->channelPricingFactory->createNew();
                $channelPricing->setChannelCode($channel->getCode());
                $channelPricing->setPrice($esVariant['price']['price']);
                $channelPricing->setOriginalPrice($esVariant['price']['original-price']);
                /** @var array{label: LocalizedField} $esAppliedPromotion */
                foreach ($esVariant['price']['applied-promotions'] as $esAppliedPromotion) {
                    $catalogPromotion = $this->catalogPromotionFactory->createNew();
                    $catalogPromotion->setCurrentLocale($localeCode);
                    $catalogPromotion->setLabel($this->getValueFromLocalizedField($esAppliedPromotion['label'], $localeCode));

                    $channelPricing->addAppliedPromotion($catalogPromotion);
                }
                $productVariant->addChannelPricing($channelPricing);
            }

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
                $productVariant->addImage($productImage);
            }
            $event = new ProductImageDocumentParserEvent($esImage, $productImage, $productResponse);
            $this->eventDispatcher->dispatch($event);

            $productResponse->addImage($productImage);
        }

        $this->taxons = [];
        /** @var array{taxon: array{sylius-id: int|string, code?: ?string, enabled: bool, left: ?int, right: ?int, level: ?int, position: ?int, root: ?array, parent: ?array, children: array[], name: array<array-key, array<string, string>>, slug: array<array-key, array<string, string>>, description: array<array-key, array<string, string>>}, position: ?int} $esProductTaxon */
        foreach ($source['product-taxons'] as $esProductTaxon) {
            $taxon = $this->getOrCreateTaxon($esProductTaxon['taxon'], $localeCode);

            $productTaxon = $this->productTaxonFactory->createNew();
            $productTaxon->setTaxon($taxon);
            $productTaxon->setProduct($productResponse);
            $productTaxon->setPosition($esProductTaxon['position']);

            $event = new ProductTaxonDocumentParserEvent($esProductTaxon, $productTaxon, $productResponse);
            $this->eventDispatcher->dispatch($event);

            $productResponse->addProductTaxon($productTaxon);
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

    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     *
     * @param array{sylius-id: int|string, code?: ?string, enabled: bool, left: ?int, right: ?int, level: ?int, position: ?int, root: ?array, parent: ?array, children: array[], name: array<array-key, array<string, string>>, slug: array<array-key, array<string, string>>, description: array<array-key, array<string, string>>} $esTaxon
     */
    private function getOrCreateTaxon(array $esTaxon, string $localeCode): TaxonInterface
    {
        if (array_key_exists($esTaxon['sylius-id'], $this->taxons)) {
            $taxon = $this->taxons[$esTaxon['sylius-id']];
        } else {
            $taxon = $this->taxonFactory->createNew();
            $this->taxons[$esTaxon['sylius-id']] = $taxon;
        }
        if (!array_key_exists('code', $esTaxon)) {
            return $taxon;
        }
        $taxon->setCode($esTaxon['code']);
        $taxon->setName($this->getValueFromLocalizedField($esTaxon['name'], $localeCode));
        $taxon->setSlug($this->getValueFromLocalizedField($esTaxon['slug'], $localeCode));
        $taxon->setDescription($this->getValueFromLocalizedField($esTaxon['description'], $localeCode));
        $taxon->setEnabled($esTaxon['enabled']);
        $taxon->setLeft($esTaxon['left']);
        $taxon->setRight($esTaxon['right']);
        $taxon->setLevel($esTaxon['level']);
        $taxon->setPosition($esTaxon['position']);
        if ($esTaxon['root'] !== null) {
            $reflectionClass = new \ReflectionClass($taxon::class);
            $rootProperty = $reflectionClass->getProperty('root');
            // @phpstan-ignore-next-line
            $rootProperty->setValue($taxon, $this->getOrCreateTaxon($esTaxon['root'], $localeCode));
        }
        if ($esTaxon['parent'] !== null) {
            // @phpstan-ignore-next-line
            $taxon->setParent($this->getOrCreateTaxon($esTaxon['parent'], $localeCode));
        }
        foreach ($esTaxon['children'] as $child) {
            // @phpstan-ignore-next-line
            $taxon->addChild($this->getOrCreateTaxon($child, $localeCode));
        }

        $event = new TaxonDocumentParserEvent($esTaxon, $taxon);
        $this->eventDispatcher->dispatch($event);

        return $taxon;
    }
}
