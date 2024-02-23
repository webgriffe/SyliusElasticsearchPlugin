<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\DocumentType;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;

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
            'variant-selection-method-abel' => $product->getVariantSelectionMethodLabel(),
            'name' => [],
            'description' => [],
            'short-description' => [],
            'slug' => [],
            'taxons' => [],
            'variants' => [],
            'main-taxon' => null,
            'attributes' => [],
            'options' => [],
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
        /** @var ProductAttributeValueInterface $attribute */
        foreach ($product->getAttributes() as $attribute) {
            $normalizedProduct['attributes'][] = $this->normalizeProductAttributeValue($attribute);
        }
        foreach ($product->getOptions() as $option) {
            $normalizedProduct['options'][] = $this->normalizeProductOption($option);
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

    private function normalizeProductAttributeValue(ProductAttributeValueInterface $attributeValue): array
    {
        $normalizedAttributeValue = [
            'sylius-id' => $attributeValue->getId(),
            'code' => $attributeValue->getCode(),
            'type' => $attributeValue->getType(),
            'value' => $attributeValue->getValue(),
            'name' => $attributeValue->getName(),
            'localeCode' => $attributeValue->getLocaleCode(),
        ];

        return $normalizedAttributeValue;
    }

    private function normalizeProductOption(ProductOptionInterface $option): array
    {
        $normalizedOption = [
            'sylius-id' => $option->getId(),
            'code' => $option->getCode(),
            'values' => $option->getValues()->toArray(),
        ];

        return $normalizedOption;
    }

    private function normalizeChannelPricing(?ChannelPricingInterface $channelPricing): ?array
    {
        if ($channelPricing === null) {
            return null;
        }

        return [
            'price' => $channelPricing->getPrice(),
            'original-price' => $channelPricing->getOriginalPrice(),
        ];
    }
}
