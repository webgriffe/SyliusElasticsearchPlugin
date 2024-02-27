<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\DocumentType;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class ProductDocumentType implements DocumentTypeInterface
{
    public const CODE = 'product';

    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private NormalizerInterface $normalizer,
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
            $documents[] = $this->normalizer->normalize($product, null, [
                'type' => 'webgriffe_sylius_elasticsearch_plugin',
                'channel' => $channel,
            ]);
        }

        return $documents;
    }

    public function getSettings(): array
    {
        return [
            'analysis' => [
                'analyzer' => [
                    'search_standard' => [
                        'type' => 'custom',
                        'tokenizer' => 'icu_tokenizer',
                        'filter' => ['lowercase', 'icu_folding', 'elision'],
                    ],
                ],
            ],
        ];
    }

    public function getMappings(): array
    {
        return [
            'properties' => [
                'sylius-id' => self::keyword(false),
                'code' => self::keyword(),
                'name' => self::nestedTranslationValues(),
                'enabled' => self::boolean(),
                'description' => self::nestedTranslationValues(),
                'short-description' => self::nestedTranslationValues(),
                'slug' => self::nestedTranslationValues(),
                'variant-selection-method' => self::text(),
                'variant-selection-method-label' => self::text(),
                'created-at' => self::date(),
                'default-variant' => [
                    'type' => 'object',
                    'dynamic' => false,
                    'enabled' => true,
                    'subobjects' => true,
                    'properties' => self::variantProperties(),
                ],
                'main-taxon' => [
                    'type' => 'object',
                    'dynamic' => false,
                    'enabled' => true,
                    'subobjects' => true,
                    'properties' => self::taxonProperties(),
                ],
                'taxons' => [
                    'type' => 'nested',
                    'dynamic' => false,
                    'include_in_parent' => true,
                    'properties' => self::taxonProperties(),
                ],
                'attributes' => [
                    'type' => 'nested',
                    'dynamic' => false,
                    'include_in_parent' => true,
                    'properties' => self::attributeProperties(),
                ],
                'images' => [
                    'type' => 'nested',
                    'dynamic' => false,
                    'include_in_parent' => true,
                    'properties' => self::imageProperties(),
                ],
                'options' => [
                    'type' => 'nested',
                    'dynamic' => false,
                    'include_in_parent' => true,
                    'properties' => self::optionProperties(),
                ],
                'variants' => [
                    'type' => 'nested',
                    'dynamic' => false,
                    'include_in_parent' => true,
                    'properties' => self::variantProperties(),
                ],
            ],
        ];
    }

    private static function keyword(bool $index = true): array
    {
        return [
            'type' => 'keyword',
            'index' => $index,
        ];
    }

    private static function boolean(bool $index = true): array
    {
        return [
            'type' => 'boolean',
            'index' => $index,
        ];
    }

    private static function text(bool $index = true): array
    {
        return [
            'type' => 'text',
            'index' => $index,
        ];
    }

    private static function integer(bool $index = true): array
    {
        return [
            'type' => 'integer',
            'index' => $index,
        ];
    }

    private static function float(bool $index = true): array
    {
        return [
            'type' => 'float',
            'index' => $index,
        ];
    }

    private static function date(bool $index = true): array
    {
        return [
            'type' => 'date',
            'index' => $index,
        ];
    }

    private static function nestedTranslationValues(bool $indexValue = true): array
    {
        return [
            'type' => 'nested',
            'dynamic' => 'false',
            'include_in_parent' => true,
            'properties' => [
                'locale' => self::text(),
                'value' => self::keyword($indexValue),
            ],
        ];
    }

    private static function taxonProperties(): array
    {
        return [
            'sylius-id' => self::keyword(false),
            'code' => self::keyword(false),
            'position' => self::integer(false),
            'name' => self::nestedTranslationValues(false),
        ];
    }

    private static function attributeProperties(): array
    {
        return [
            'sylius-id' => self::keyword(false),
            'code' => self::keyword(false),
            'type' => self::text(false),
            'storage-type' => self::text(false),
            'position' => self::integer(false),
            'translatable' => self::boolean(false),
            'name' => self::nestedTranslationValues(false),
            'values' => [
                'type' => 'nested',
                'dynamic' => false,
                'include_in_parent' => true,
                'properties' => self::attributeValueProperties(),
            ],
        ];
    }

    private static function attributeValueProperties(): array
    {
        return [
            'sylius-id' => self::keyword(false),
            'code' => self::keyword(false),
            'locale' => self::text(false),
            'checkbox-value' => self::keyword(),
            'date-value' => self::keyword(),
            'datetime-value' => self::keyword(),
            'integer-value' => self::integer(),
            'percent-value' => self::float(),
            'select-value' => self::keyword(),
            'textarea-value' => self::keyword(),
            'text-value' => self::keyword(),
        ];
    }

    private static function imageProperties(): array
    {
        return [
            'sylius-id' => self::keyword(false),
            'type' => self::text(false),
            'path' => self::text(false),
            'variants' => [
                'type' => 'nested',
                'dynamic' => false,
                'include_in_parent' => true,
                'properties' => self::imageVariantsProperties(),
            ],
        ];
    }

    private static function imageVariantsProperties(): array
    {
        return [
            'sylius-id' => self::keyword(false),
            'code' => self::keyword(false),
        ];
    }

    private static function optionProperties(): array
    {
        return [
            'sylius-id' => self::keyword(false),
            'code' => self::keyword(false),
            'name' => self::nestedTranslationValues(false),
            'values' => [
                'type' => 'nested',
                'dynamic' => false,
                'include_in_parent' => true,
                'properties' => self::optionValueProperties(),
            ],
        ];
    }

    private static function optionValueProperties(): array
    {
        return [
            'sylius-id' => self::keyword(false),
            'code' => self::keyword(false),
            'value' => self::keyword(),
            'name' => self::nestedTranslationValues(false),
        ];
    }

    private static function variantProperties(): array
    {
        return [
            'sylius-id' => self::keyword(false),
            'code' => self::keyword(),
            'enabled' => self::boolean(),
            'position' => self::integer(),
            'weight' => self::float(false),
            'width' => self::float(false),
            'height' => self::float(false),
            'depth' => self::float(false),
            'shipping-required' => self::boolean(false),
            'name' => self::nestedTranslationValues(),
            'price' => [
                'type' => 'object',
                'dynamic' => false,
                'enabled' => true,
                'subobjects' => true,
                'properties' => self::priceProperties(),
            ],
        ];
    }

    private static function priceProperties(): array
    {
        return [
            'price' => self::integer(false),
            'original-price' => self::integer(false),
            'applied-promotions' => [
                'type' => 'nested',
                'dynamic' => false,
                'include_in_parent' => true,
                'properties' => self::catalogPromotionProperties(),
            ],
        ];
    }

    private static function catalogPromotionProperties(): array
    {
        return [
            'sylius-id' => self::keyword(false),
            'code' => self::keyword(),
            'label' => self::nestedTranslationValues(),
            'description' => self::nestedTranslationValues(),
        ];
    }
}
