<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\DocumentType;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webgriffe\SyliusElasticsearchPlugin\Repository\DocumentTypeRepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class ProductDocumentType implements DocumentTypeInterface
{
    public const CODE = 'product';

    /**
     * @param RepositoryInterface<LocaleInterface> $localeRepository
     */
    public function __construct(
        private DocumentTypeRepositoryInterface $documentTypeRepository,
        private NormalizerInterface $normalizer,
        private RepositoryInterface $localeRepository,
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
        /** @var ProductInterface $documentToIndex */
        foreach ($this->documentTypeRepository->findDocumentsToIndex() as $documentToIndex) {
            $documents[] = $this->normalizer->normalize($documentToIndex, null, [
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
                'sylius-id' => $this->keyword(false),
                'code' => $this->keyword(),
                'name' => $this->nestedTranslationValues(),
                'enabled' => $this->boolean(),
                'description' => $this->nestedTranslationValues(),
                'short-description' => $this->nestedTranslationValues(),
                'slug' => $this->nestedTranslationValues(),
                'variant-selection-method' => $this->keyword(),
                'variant-selection-method-label' => $this->keyword(),
                'created-at' => $this->date(),
                'default-variant' => [
                    'type' => 'object',
                    'dynamic' => false,
                    'enabled' => true,
                    // 'subobjects' => true, ES v8
                    'properties' => $this->variantProperties(),
                ],
                'main-taxon' => [
                    'type' => 'object',
                    'dynamic' => false,
                    'enabled' => true,
                    // 'subobjects' => true, ES v8
                    'properties' => $this->taxonProperties(),
                ],
                'taxons' => [
                    'type' => 'nested',
                    'dynamic' => false,
                    'include_in_parent' => true,
                    'properties' => $this->taxonProperties(),
                ],
                'attributes' => [
                    'type' => 'nested',
                    'dynamic' => false,
                    'include_in_parent' => true,
                    'properties' => $this->attributeProperties(),
                ],
                'images' => [
                    'type' => 'nested',
                    'dynamic' => false,
                    'include_in_parent' => true,
                    'properties' => $this->imageProperties(),
                ],
                'variants' => [
                    'type' => 'nested',
                    'dynamic' => false,
                    'include_in_parent' => true,
                    'properties' => $this->variantProperties(),
                ],
            ],
        ];
    }

    private function keyword(bool $index = true): array
    {
        return [
            'type' => 'keyword',
            'index' => $index,
        ];
    }

    private function boolean(bool $index = true): array
    {
        return [
            'type' => 'boolean',
            'index' => $index,
        ];
    }

    private function integer(bool $index = true): array
    {
        return [
            'type' => 'integer',
            'index' => $index,
        ];
    }

    private function float(bool $index = true): array
    {
        return [
            'type' => 'float',
            'index' => $index,
        ];
    }

    private function date(bool $index = true): array
    {
        return [
            'type' => 'date',
            'index' => $index,
        ];
    }

    private function nestedTranslationValues(bool $indexValue = true): array
    {
        $locales = $this->localeRepository->findAll();
        $properties = [];
        foreach ($locales as $locale) {
            $localeCode = $locale->getCode();
            Assert::string($localeCode);
            $properties[$localeCode] = $this->keyword($indexValue);
        }

        return [
            'type' => 'nested',
            'dynamic' => 'false',
            'include_in_parent' => true,
            'properties' => $properties,
        ];
    }

    private function taxonProperties(): array
    {
        return [
            'sylius-id' => $this->keyword(),
            'code' => $this->keyword(false),
            'position' => $this->integer(false),
            'name' => $this->nestedTranslationValues(false),
        ];
    }

    private function attributeProperties(): array
    {
        return [
            'sylius-id' => $this->keyword(false),
            'code' => $this->keyword(),
            'type' => $this->keyword(false),
            'storage-type' => $this->keyword(false),
            'position' => $this->integer(false),
            'translatable' => $this->boolean(false),
            'name' => $this->nestedTranslationValues(false),
            'values' => [
                'type' => 'nested',
                'dynamic' => false,
                'include_in_parent' => true,
                'properties' => $this->attributeValueProperties(),
            ],
        ];
    }

    private function attributeValueProperties(): array
    {
        return [
            'sylius-id' => $this->keyword(false),
            'code' => $this->keyword(false),
            'locale' => $this->keyword(false),
            'checkbox-value' => $this->keyword(),
            'date-value' => $this->keyword(),
            'datetime-value' => $this->keyword(),
            'integer-value' => $this->integer(),
            'percent-value' => $this->float(),
            'select-value' => $this->keyword(),
            'textarea-value' => $this->keyword(),
            'text-value' => $this->keyword(),
        ];
    }

    private function imageProperties(): array
    {
        return [
            'sylius-id' => $this->keyword(false),
            'type' => $this->keyword(false),
            'path' => $this->keyword(false),
            'variants' => [
                'type' => 'nested',
                'dynamic' => false,
                'include_in_parent' => true,
                'properties' => $this->imageVariantsProperties(),
            ],
        ];
    }

    private function imageVariantsProperties(): array
    {
        return [
            'sylius-id' => $this->keyword(false),
            'code' => $this->keyword(false),
        ];
    }

    private function optionProperties(): array
    {
        return [
            'sylius-id' => $this->keyword(false),
            'code' => $this->keyword(),
            'name' => $this->nestedTranslationValues(false),
            'value' => [
                'type' => 'object',
                'dynamic' => false,
                'enabled' => true,
                // 'subobjects' => true, ES v8
                'properties' => $this->optionValueProperties(),
            ],
        ];
    }

    private function optionValueProperties(): array
    {
        return [
            'sylius-id' => $this->keyword(false),
            'code' => $this->keyword(false),
            'value' => $this->keyword(),
            'name' => $this->nestedTranslationValues(false),
        ];
    }

    private function variantProperties(): array
    {
        return [
            'sylius-id' => $this->keyword(false),
            'code' => $this->keyword(),
            'enabled' => $this->boolean(),
            'position' => $this->integer(),
            'weight' => $this->float(false),
            'width' => $this->float(false),
            'height' => $this->float(false),
            'depth' => $this->float(false),
            'shipping-required' => $this->boolean(false),
            'name' => $this->nestedTranslationValues(),
            'price' => [
                'type' => 'object',
                'dynamic' => false,
                'enabled' => true,
                // 'subobjects' => true, ES v8
                'properties' => $this->priceProperties(),
            ],
            'options' => [
                'type' => 'nested',
                'dynamic' => false,
                'include_in_parent' => true,
                'properties' => $this->optionProperties(),
            ],
        ];
    }

    private function priceProperties(): array
    {
        return [
            'price' => $this->integer(false),
            'original-price' => $this->integer(false),
            'applied-promotions' => [
                'type' => 'nested',
                'dynamic' => false,
                'include_in_parent' => true,
                'properties' => $this->catalogPromotionProperties(),
            ],
        ];
    }

    private function catalogPromotionProperties(): array
    {
        return [
            'sylius-id' => $this->keyword(false),
            'code' => $this->keyword(),
            'label' => $this->nestedTranslationValues(),
            'description' => $this->nestedTranslationValues(),
        ];
    }
}
