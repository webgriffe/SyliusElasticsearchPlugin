<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Serializer;

use DateTimeInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductAttributeTranslationInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionTranslationInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Product\Model\ProductOptionValueTranslationInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentType\ProductDocumentTypeProductNormalizeEvent;
use Webgriffe\SyliusElasticsearchPlugin\Model\FilterableInterface;
use Webmozart\Assert\Assert;

/**
 * @final
 */
class ProductNormalizer implements NormalizerInterface
{
    /** @var string[] */
    private array $localeCodes = [];

    /** @var array<array-key, array{input: string, weight: positive-int}> */
    private array $productSuggesters = [];

    /** @var array<string|int, array> */
    private array $serializedTaxon = [];

    private ?string $channelDefaultLocaleCode = null;

    public function __construct(
        private readonly ProductVariantResolverInterface $productVariantResolver,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly NormalizerInterface $serializer,
        private readonly string $systemDefaultLocaleCode,
    ) {
    }

    /**
     * @param ProductInterface|mixed $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $channel = $context['channel'];
        $product = $object;
        Assert::isInstanceOf($product, ProductInterface::class);
        Assert::isInstanceOf($channel, ChannelInterface::class);

        foreach ($channel->getLocales() as $locale) {
            $localeCode = $locale->getCode();
            Assert::string($localeCode);
            $this->localeCodes[] = $localeCode;
        }
        $channelDefaultLocale = $channel->getDefaultLocale();
        if ($channelDefaultLocale !== null) {
            $this->channelDefaultLocaleCode = $channelDefaultLocale->getCode();
        }
        $this->productSuggesters = [];
        $this->serializedTaxon = [];

        $normalizedProduct = [
            'sylius-id' => $product->getId(),
            'code' => $product->getCode(),
            'enabled' => $product->isEnabled(),
            'variant-selection-method' => $product->getVariantSelectionMethod(),
            'variant-selection-method-label' => $product->getVariantSelectionMethodLabel(),
            'created-at' => $product->getCreatedAt()?->format('c'),
            'name-as-keyword' => [],
            'name' => [],
            'description' => [],
            'short-description' => [],
            'slug' => [],
            'meta-keywords' => [],
            'meta-description' => [],
            'taxons' => [],
            'variants' => [],
            'default-variant' => null,
            'main-taxon' => null,
            'attributes' => [],
            'product-options' => [],
            'images' => [],
            'suggest' => [],
        ];
        $this->productSuggesters[] = ['input' => (string) $product->getCode(), 'weight' => 100];
        /** @var ProductTranslationInterface $productTranslation */
        foreach ($product->getTranslations() as $productTranslation) {
            $localeCode = $productTranslation->getLocale();
            Assert::string($localeCode);
            $productName = (string) $productTranslation->getName();
            $normalizedProduct['name-as-keyword'][] = [
                $localeCode => $productName,
            ];
            $normalizedProduct['name'][] = [
                $localeCode => $productName,
            ];
            $normalizedProduct['description'][] = [
                $localeCode => $productTranslation->getDescription(),
            ];
            $normalizedProduct['short-description'][] = [
                $localeCode => $productTranslation->getShortDescription(),
            ];
            $normalizedProduct['slug'][] = [
                $localeCode => $productTranslation->getSlug(),
            ];
            $normalizedProduct['meta-keywords'][] = [
                $localeCode => $productTranslation->getMetaKeywords(),
            ];
            $normalizedProduct['meta-description'][] = [
                $localeCode => $productTranslation->getMetaDescription(),
            ];
            $this->productSuggesters[] = ['input' => $productName, 'weight' => 50];
        }
        $defaultVariant = $this->productVariantResolver->getVariant($product);
        if ($defaultVariant instanceof ProductVariantInterface) {
            $normalizedProduct['default-variant'] = $this->serializer->normalize($defaultVariant, $format, $context);
        }
        $mainTaxon = $product->getMainTaxon();
        if ($mainTaxon instanceof TaxonInterface) {
            $normalizedProduct['main-taxon'] = $this->normalizeTaxon($mainTaxon, $format, $context);
        }
        foreach ($product->getProductTaxons() as $productTaxon) {
            $normalizedProduct['product-taxons'][] = $this->normalizeProductTaxon($productTaxon, $format, $context);
        }
        /** @var ProductVariantInterface $variant */
        foreach ($product->getVariants() as $variant) {
            $normalizedProduct['variants'][] = $this->serializer->normalize($variant, $format, $context);
        }

        // Product attributes indexing for filters

        /** @var array<string|int, array{attribute: ProductAttributeInterface, values: ProductAttributeValueInterface[]}> $translatedAttributes */
        $translatedAttributes = [];

        /** @var ProductAttributeValueInterface $attributeValue */
        foreach ($product->getAttributes() as $attributeValue) {
            $attribute = $attributeValue->getAttribute();
            Assert::isInstanceOf($attribute, ProductAttributeInterface::class);

            $attributeId = $attribute->getId();
            if (!is_string($attributeId) && !is_int($attributeId)) {
                throw new RuntimeException('Attribute ID different from string or integer is not supported.');
            }

            if (!array_key_exists($attributeId, $translatedAttributes)) {
                $translatedAttributes[$attributeId] = [
                    'attribute' => $attribute,
                    'values' => [],
                ];
            }
            $translatedAttributes[$attributeId]['values'][] = $attributeValue;
        }
        foreach ($translatedAttributes as $attribute) {
            $normalizedProduct['attributes'][] = $this->normalizeAttributeWithValues($attribute);
        }

        // Product options indexing for filters

        /** @var array<string|int, array{option: ProductOptionInterface, values: array<array-key, ProductOptionValueInterface>}> $optionsWithValues */
        $optionsWithValues = [];

        /** @var ProductVariantInterface $variant */
        foreach ($product->getEnabledVariants() as $variant) {
            foreach ($variant->getOptionValues() as $optionValue) {
                $option = $optionValue->getOption();
                Assert::isInstanceOf($option, ProductOptionInterface::class);

                $optionId = $option->getId();
                if (!is_string($optionId) && !is_int($optionId)) {
                    throw new RuntimeException('Option ID different from string or integer is not supported.');
                }
                $optionValueId = $optionValue->getId();
                if (!is_string($optionValueId) && !is_int($optionValueId)) {
                    throw new RuntimeException('Option value ID different from string or integer is not supported.');
                }

                if (!array_key_exists($optionId, $optionsWithValues)) {
                    $optionsWithValues[$optionId] = [
                        'option' => $option,
                        'values' => [],
                    ];
                }
                if (array_key_exists($optionValueId, $optionsWithValues[$optionId]['values'])) {
                    continue;
                }
                $optionsWithValues[$optionId]['values'][$optionValueId] = $optionValue;
            }
        }
        foreach ($optionsWithValues as $optionWithValues) {
            $normalizedProduct['product-options'][] = $this->normalizeOptionWithValues($optionWithValues);
        }

        /** @var ProductImageInterface $image */
        foreach ($product->getImages() as $image) {
            $normalizedProduct['images'][] = $this->normalizeProductImage($image);
        }
        $normalizedProduct['suggest'] = $this->productSuggesters;

        $event = new ProductDocumentTypeProductNormalizeEvent($product, $channel, $normalizedProduct);
        $this->eventDispatcher->dispatch($event);

        return $event->getNormalizedProduct();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ProductInterface::class => true];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ProductInterface &&
            array_key_exists('type', $context) &&
            $context['type'] === 'webgriffe_sylius_elasticsearch_plugin'
        ;
    }

    private function normalizeTaxon(TaxonInterface $taxon, ?string $format = null, array $context = []): array
    {
        $taxonId = $taxon->getId();
        if (!is_string($taxonId) && !is_int($taxonId)) {
            throw new RuntimeException('Taxon ID different from string or integer is not supported.');
        }
        if (array_key_exists($taxonId, $this->serializedTaxon)) {
            return $this->serializedTaxon[$taxonId];
        }
        $normalizedTaxon = $this->serializer->normalize($taxon, $format, $context);
        Assert::isArray($normalizedTaxon);
        $this->serializedTaxon[$taxonId] = $normalizedTaxon;
        /** @var TaxonTranslationInterface $taxonTranslation */
        foreach ($taxon->getTranslations() as $taxonTranslation) {
            $this->productSuggesters[] = ['input' => (string) $taxonTranslation->getName(), 'weight' => 10];
        }

        return $this->serializedTaxon[$taxonId];
    }

    private function normalizeProductTaxon(
        ProductTaxonInterface $productTaxon,
        ?string $format = null,
        array $context = [],
    ): array {
        $taxon = $productTaxon->getTaxon();
        Assert::isInstanceOf($taxon, TaxonInterface::class);

        return [
            'taxon' => $this->normalizeTaxon($taxon, $format, $context),
            'position' => $productTaxon->getPosition(),
        ];
    }

    /**
     * @param array{attribute: ProductAttributeInterface, values: ProductAttributeValueInterface[]} $attributeWithValues
     */
    private function normalizeAttributeWithValues(array $attributeWithValues): array
    {
        $attribute = $attributeWithValues['attribute'];
        $isTranslatable = $attribute->isTranslatable();
        $filterable = false;
        if ($attribute instanceof FilterableInterface) {
            $filterable = $attribute->isFilterable();
        }
        $normalizedAttributeValue = [
            'sylius-id' => $attribute->getId(),
            'code' => $attribute->getCode(),
            'type' => $attribute->getType(),
            'storage-type' => $attribute->getStorageType(),
            'position' => $attribute->getPosition(),
            'translatable' => $isTranslatable,
            'filterable' => $filterable,
            'name' => [],
            'values' => [],
        ];
        /** @var ProductAttributeTranslationInterface $attributeTranslation */
        foreach ($attribute->getTranslations() as $attributeTranslation) {
            $localeCode = $attributeTranslation->getLocale();
            Assert::string($localeCode);
            $normalizedAttributeValue['name'][] = [
                $localeCode => $attributeTranslation->getName(),
            ];
        }
        $fallbackValue = null;
        foreach ($attributeWithValues['values'] as $attributeValue) {
            $localeCode = $attributeValue->getLocaleCode();
            if ($localeCode === null) {
                $localeCode = $this->channelDefaultLocaleCode;
            }
            Assert::string($localeCode);
            $value = $this->normalizeAttributeValue($attributeValue);
            $normalizedAttributeValue['values'][$localeCode][] = $value;
            if ($localeCode === $this->channelDefaultLocaleCode) {
                $fallbackValue = $value;
            }
            if ($fallbackValue === null && $localeCode === $this->systemDefaultLocaleCode) {
                $fallbackValue = $value;
            }
        }
        if ($fallbackValue !== null) {
            foreach ($this->localeCodes as $localeCode) {
                if (!array_key_exists($localeCode, $normalizedAttributeValue['values'])) {
                    $normalizedAttributeValue['values'][$localeCode][] = $fallbackValue;
                }
            }
        }

        return $normalizedAttributeValue;
    }

    private function normalizeAttributeValue(ProductAttributeValueInterface $attributeValue): array
    {
        $localeCode = $attributeValue->getLocaleCode();
        $attribute = $attributeValue->getAttribute();
        Assert::isInstanceOf($attribute, ProductAttributeInterface::class);
        $storageType = $attribute->getStorageType();
        Assert::stringNotEmpty($storageType);

        $attributeValueValue = $attributeValue->getValue();
        if ($storageType === AttributeValueInterface::STORAGE_JSON) {
            $attributeValueToIndex = [];
            /** @var array<string, array<string, ?string>> $allAttributeValues */
            $allAttributeValues = $attribute->getConfiguration()['choices'];
            /** @var string|array<array-key, string> $attributeValueValues */
            $attributeValueValues = $attributeValueValue;
            if (is_iterable($attributeValueValues)) {
                foreach ($attributeValueValues as $value) {
                    if ($localeCode !== null && array_key_exists($localeCode, $allAttributeValues[$value]) &&
                        $allAttributeValues[$value][$localeCode] !== null
                    ) {
                        $attributeValueToIndex[] = $allAttributeValues[$value][$localeCode];
                    } elseif ($this->channelDefaultLocaleCode !== null && array_key_exists($this->channelDefaultLocaleCode, $allAttributeValues[$value])) {
                        $attributeValueToIndex[] = $allAttributeValues[$value][$this->channelDefaultLocaleCode];
                    } else {
                        $attributeValueToIndex[] = $allAttributeValues[$value][$this->systemDefaultLocaleCode];
                    }
                }
            } else {
                if ($localeCode !== null && $allAttributeValues[$attributeValueValues][$localeCode] !== null) {
                    $attributeValueToIndex[] = $allAttributeValues[$attributeValueValues][$localeCode] . ', ';
                } elseif ($this->channelDefaultLocaleCode !== null && $allAttributeValues[$attributeValueValues][$this->channelDefaultLocaleCode] !== null) {
                    $attributeValueToIndex[] = $allAttributeValues[$attributeValueValues][$this->channelDefaultLocaleCode];
                } else {
                    $attributeValueToIndex[] = $allAttributeValues[$attributeValueValues][$this->systemDefaultLocaleCode];
                }
            }
        } elseif ($storageType === AttributeValueInterface::STORAGE_DATE) {
            Assert::isInstanceOf($attributeValueValue, DateTimeInterface::class);
            $attributeValueToIndex = [$attributeValueValue->format('Y-m-d')];
        } elseif ($storageType === AttributeValueInterface::STORAGE_DATETIME) {
            Assert::isInstanceOf($attributeValueValue, DateTimeInterface::class);
            $attributeValueToIndex = [$attributeValueValue->format('Y-m-d H:i:s')];
        } else {
            // Limit the length of the attribute value to 32766 bytes to avoid Elasticsearch error
            if (is_string($attributeValueValue) && function_exists('mb_strcut')) {
                $attributeValueValue = mb_strcut($attributeValueValue, 0, 32766, 'UTF-8');
            }
            $attributeValueToIndex = [$attributeValueValue];
        }

        return [
            'sylius-id' => $attributeValue->getId(),
            'code' => $attributeValue->getCode(),
            'locale' => $localeCode,
            'values' => $attributeValueToIndex,
        ];
    }

    /**
     * @param array{option: ProductOptionInterface, values: ProductOptionValueInterface[]} $optionWithValues
     */
    private function normalizeOptionWithValues(array $optionWithValues): array
    {
        $option = $optionWithValues['option'];
        $filterable = false;
        if ($option instanceof FilterableInterface) {
            $filterable = $option->isFilterable();
        }
        $normalizedOptionValue = [
            'sylius-id' => $option->getId(),
            'code' => $option->getCode(),
            'name' => [],
            'position' => $option->getPosition(),
            'filterable' => $filterable,
            'values' => [],
        ];
        /** @var ProductOptionTranslationInterface $optionTranslation */
        foreach ($option->getTranslations() as $optionTranslation) {
            $localeCode = $optionTranslation->getLocale();
            Assert::string($localeCode);
            $normalizedOptionValue['name'][] = [
                $localeCode => $optionTranslation->getName(),
            ];
        }
        foreach ($optionWithValues['values'] as $optionValue) {
            $normalizedOptionValue['values'][] = $this->normalizeProductOptionValue($optionValue);
        }

        return $normalizedOptionValue;
    }

    private function normalizeProductImage(ProductImageInterface $image): array
    {
        $normalizedImage = [
            'sylius-id' => $image->getId(),
            'type' => $image->getType(),
            'path' => $image->getPath(),
            'variants' => [],
        ];
        foreach ($image->getProductVariants() as $productVariant) {
            $normalizedImage['variants'][] = [
                'sylius-id' => $productVariant->getId(),
                'code' => $productVariant->getCode(),
            ];
        }

        return $normalizedImage;
    }

    private function normalizeProductOptionValue(ProductOptionValueInterface $optionValue): array
    {
        $normalizedOptionValue = [
            'sylius-id' => $optionValue->getId(),
            'code' => $optionValue->getCode(),
            'value' => $optionValue->getValue(),
            'name' => [],
        ];
        /** @var ProductOptionValueTranslationInterface $optionValueTranslation */
        foreach ($optionValue->getTranslations() as $optionValueTranslation) {
            $localeCode = $optionValueTranslation->getLocale();
            Assert::string($localeCode);
            $normalizedOptionValue['name'][] = [
                $localeCode => $optionValueTranslation->getValue(),
            ];
        }

        return $normalizedOptionValue;
    }
}
