<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\DocumentType;

use RuntimeException;
use Sylius\Component\Core\Model\CatalogPromotionInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductAttributeTranslationInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Product\Model\ProductOptionValueTranslationInterface;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;
use Sylius\Component\Promotion\Model\CatalogPromotionTranslationInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;
use Webmozart\Assert\Assert;

final readonly class ProductDocumentType implements DocumentTypeInterface
{
    public const CODE = 'product';

    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    public function getCode(): string
    {
        return self::CODE;
    }

    /**
     * @TODO Add custom method on repository to get products to index
     * @TODO Use serializer to transform product to array
     */
    public function getDocuments(ChannelInterface $channel): array
    {
        $documents = [];
        /** @var ProductInterface $product */
        foreach ($this->productRepository->findAll() as $product) {
            $documents[] = $this->normalizeProduct($product, $channel);
        }

        return $documents;
    }

    public function getMappings(): array
    {
        return [
            'properties' => [
                'sylius-id' => [
                    'type' => 'keyword',
                    'index' => false,
                ],
                'code' => [
                    'type' => 'keyword',
                ],
                'name' => [
                    'type' => 'nested',
                    'dynamic' => 'false',
                    'include_in_parent' => true,
                    'properties' => [
                        'locale' => [
                            'type' => 'text',
                            'index' => false,
                        ],
                        'value' => [
                            'type' => 'keyword',
                        ],
                    ],
                ],
                'description' => [
                    'type' => 'nested',
                    'dynamic' => 'false',
                    'include_in_parent' => true,
                    'properties' => [
                        'locale' => [
                            'type' => 'text',
                            'index' => false,
                        ],
                        'value' => [
                            'type' => 'keyword',
                        ],
                    ],
                ],
                'slug' => [
                    'type' => 'nested',
                    'dynamic' => 'false',
                    'include_in_parent' => true,
                    'properties' => [
                        'locale' => [
                            'type' => 'text',
                            'index' => false,
                        ],
                        'value' => [
                            'type' => 'keyword',
                        ],
                    ],
                ],
                'main_taxon' => [
                    'type' => 'object',
                    'dynamic' => false,
                    'enabled' => true,
                    'subobjects' => true,
                    'properties' => [
                        'sylius-id' => [
                            'type' => 'keyword',
                            'index' => false,
                        ],
                        'code' => [
                            'type' => 'keyword',
                            'index' => false,
                        ],
                        'name' => [
                            'type' => 'nested',
                            'dynamic' => 'false',
                            'include_in_parent' => true,
                            'properties' => [
                                'locale' => [
                                    'type' => 'text',
                                    'index' => false,
                                ],
                                'value' => [
                                    'type' => 'keyword',
                                    'index' => false,
                                ],
                            ],
                        ],
                    ],
                ],
                'taxons' => [
                    'type' => 'nested',
                    'dynamic' => 'false',
                    'include_in_parent' => true,
                    'properties' => [
                        'sylius-id' => [
                            'type' => 'keyword',
                            'index' => false,
                        ],
                        'code' => [
                            'type' => 'keyword',
                            'index' => false,
                        ],
                        'name' => [
                            'type' => 'nested',
                            'dynamic' => 'false',
                            'include_in_parent' => true,
                            'properties' => [
                                'locale' => [
                                    'type' => 'text',
                                    'index' => false,
                                ],
                                'value' => [
                                    'type' => 'keyword',
                                    'index' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function normalizeProduct(ProductInterface $product, ChannelInterface $channel): array
    {
        $normalizedProduct = [
            'sylius-id' => $product->getId(),
            'code' => $product->getCode(),
            'enabled' => $product->isEnabled(),
            'variant-selection-method' => $product->getVariantSelectionMethod(),
            'variant-selection-method-label' => $product->getVariantSelectionMethodLabel(),
            'name' => [],
            'description' => [],
            'short-description' => [],
            'slug' => [],
            'taxons' => [],
            'variants' => [],
            'main-taxon' => null,
            'attributes' => [],
            'options' => [],
            'images' => [],
        ];
        /** @var ProductTranslationInterface $productTranslation */
        foreach ($product->getTranslations() as $productTranslation) {
            $normalizedProduct['name'][] = [
                'locale' => $productTranslation->getLocale(),
                'value' => $productTranslation->getName(),
            ];
            $normalizedProduct['description'][] = [
                'locale' => $productTranslation->getLocale(),
                'value' => $productTranslation->getDescription(),
            ];
            $normalizedProduct['short-description'][] = [
                'locale' => $productTranslation->getLocale(),
                'value' => $productTranslation->getShortDescription(),
            ];
            $normalizedProduct['slug'][] = [
                'locale' => $productTranslation->getLocale(),
                'value' => $productTranslation->getSlug(),
            ];
        }
        $mainTaxon = $product->getMainTaxon();
        if ($mainTaxon instanceof TaxonInterface) {
            $normalizedProduct['main-taxon'] = $this->normalizeTaxon($mainTaxon);
        }
        foreach ($product->getTaxons() as $taxon) {
            $normalizedProduct['taxons'][] = $this->normalizeTaxon($taxon);
        }
        /** @var ProductVariantInterface $variant */
        foreach ($product->getVariants() as $variant) {
            $normalizedProduct['variants'][] = $this->normalizeProductVariant($variant, $channel);
        }

        /** @var array<string|int, array{attribute: ProductAttributeInterface, values: ProductAttributeValueInterface[]}> $attributes */
        $attributes = [];

        /** @var ProductAttributeValueInterface $attributeValue */
        foreach ($product->getAttributes() as $attributeValue) {
            $attribute = $attributeValue->getAttribute();
            Assert::isInstanceOf($attribute, ProductAttributeInterface::class);

            $attributeId = $attribute->getId();
            if (!is_string($attributeId) && !is_int($attributeId)) {
                throw new RuntimeException('Attribute ID different from string or integer is not supported.');
            }
            if (!array_key_exists($attributeId, $attributes)) {
                $attributes[$attributeId] = [
                    'attribute' => $attribute,
                    'values' => [],
                ];
            }
            $attributes[$attributeId]['values'][] = $attributeValue;
        }
        foreach ($attributes as $attribute) {
            $normalizedProduct['attributes'][] = $this->normalizeAttribute($attribute);
        }

        foreach ($product->getOptions() as $option) {
            $normalizedProduct['options'][] = $this->normalizeProductOption($option);
        }
        /** @var ProductImageInterface $image */
        foreach ($product->getImages() as $image) {
            $normalizedProduct['images'][] = $this->normalizeProductImage($image);
        }

        return $normalizedProduct;
    }

    private function normalizeTaxon(TaxonInterface $taxon): array
    {
        $normalizedTaxon = [
            'sylius-id' => $taxon->getId(),
            'code' => $taxon->getCode(),
            'name' => [],
        ];
        /** @var TaxonTranslationInterface $taxonTranslation */
        foreach ($taxon->getTranslations() as $taxonTranslation) {
            $normalizedTaxon['name'][] = [
                'locale' => $taxonTranslation->getLocale(),
                'value' => $taxonTranslation->getName(),
            ];
        }

        return $normalizedTaxon;
    }

    private function normalizeProductVariant(ProductVariantInterface $variant, ChannelInterface $channel): array
    {
        $normalizedVariant = [
            'sylius-id' => $variant->getId(),
            'code' => $variant->getCode(),
            'weight' => $variant->getWeight(),
            'width' => $variant->getWidth(),
            'height' => $variant->getHeight(),
            'depth' => $variant->getDepth(),
            'shipping-required' => $variant->isShippingRequired(),
            'name' => [],
            'price' => $this->normalizeChannelPricing($variant->getChannelPricingForChannel($channel)),
        ];

        /** @var ProductVariantTranslationInterface $variantTranslation */
        foreach ($variant->getTranslations() as $variantTranslation) {
            $normalizedVariant['name'][] = [
                'locale' => $variantTranslation->getLocale(),
                'value' => $variantTranslation->getName(),
            ];
        }

        return $normalizedVariant;
    }

    /**
     * @param array{attribute: ProductAttributeInterface, values: ProductAttributeValueInterface[]} $attributeWithValues
     */
    private function normalizeAttribute(array $attributeWithValues): array
    {
        $attribute = $attributeWithValues['attribute'];
        $normalizedAttributeValue = [
            'sylius-id' => $attribute->getId(),
            'code' => $attribute->getCode(),
            'type' => $attribute->getType(),
            'storage-type' => $attribute->getStorageType(),
            'position' => $attribute->getPosition(),
            'translatable' => $attribute->isTranslatable(),
            'name' => [],
            'values' => [],
        ];
        /** @var ProductAttributeTranslationInterface $attributeTranslation */
        foreach ($attribute->getTranslations() as $attributeTranslation) {
            $normalizedAttributeValue['name'][] = [
                'locale' => $attributeTranslation->getLocale(),
                'value' => $attributeTranslation->getName(),
            ];
        }
        foreach ($attributeWithValues['values'] as $attributeValue) {
            $normalizedAttributeValue['values'][] = $this->normalizeAttributeValue($attributeValue);
        }

        return $normalizedAttributeValue;
    }

    private function normalizeAttributeValue(ProductAttributeValueInterface $attributeValue): array
    {
        $attribute = $attributeValue->getAttribute();
        Assert::isInstanceOf($attribute, ProductAttributeInterface::class);
        $storageType = $attribute->getStorageType();
        Assert::stringNotEmpty($storageType);

        return [
            'sylius-id' => $attributeValue->getId(),
            'code' => $attributeValue->getCode(),
            'locale' => $attributeValue->getLocaleCode(),
            $storageType . '-value' => $attributeValue->getValue(),
        ];
    }

    private function normalizeProductOption(ProductOptionInterface $option): array
    {
        $normalizedOption = [
            'sylius-id' => $option->getId(),
            'code' => $option->getCode(),
            'values' => [],
        ];
        foreach ($option->getValues() as $optionValue) {
            $normalizedOptionValue = [
                'sylius-id' => $optionValue->getId(),
                'code' => $optionValue->getCode(),
                'value' => $optionValue->getValue(),
                'name' => [],
            ];
            /** @var ProductOptionValueTranslationInterface $optionValueTranslation */
            foreach ($optionValue->getTranslations() as $optionValueTranslation) {
                $normalizedOptionValue['name'][] = [
                    'locale' => $optionValueTranslation->getLocale(),
                    'value' => $optionValueTranslation->getValue(),
                ];
            }
            $normalizedOption['values'][] = $normalizedOptionValue;
        }

        return $normalizedOption;
    }

    private function normalizeChannelPricing(?ChannelPricingInterface $channelPricing): ?array
    {
        if ($channelPricing === null) {
            return null;
        }
        $normalizedChannelPricing = [
            'price' => $channelPricing->getPrice(),
            'original-price' => $channelPricing->getOriginalPrice(),
            'applied-promotions' => [],
        ];
        /** @var CatalogPromotionInterface $catalogPromotion */
        foreach ($channelPricing->getAppliedPromotions() as $catalogPromotion) {
            $normalizedCatalogPromotion = [
                'sylius-id' => $catalogPromotion->getId(),
                'code' => $catalogPromotion->getCode(),
                'label' => [],
                'description' => [],
            ];
            /** @var CatalogPromotionTranslationInterface $catalogPromotionTranslation */
            foreach ($catalogPromotion->getTranslations() as $catalogPromotionTranslation) {
                $normalizedCatalogPromotion['label'][] = [
                    'locale' => $catalogPromotionTranslation->getLocale(),
                    'value' => $catalogPromotionTranslation->getLabel(),
                ];
                $normalizedCatalogPromotion['description'][] = [
                    'locale' => $catalogPromotionTranslation->getLocale(),
                    'value' => $catalogPromotionTranslation->getDescription(),
                ];
            }

            $normalizedChannelPricing['applied-promotions'][] = $normalizedCatalogPromotion;
        }

        return $normalizedChannelPricing;
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
}
