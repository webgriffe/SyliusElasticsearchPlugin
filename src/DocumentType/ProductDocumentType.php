<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\DocumentType;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentType\ProductDocumentTypeMappingsEvent;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentType\ProductDocumentTypeSettingsEvent;
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
        private EventDispatcherInterface $eventDispatcher,
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
        foreach ($this->documentTypeRepository->findDocumentsToIndex($channel) as $documentToIndex) {
            $documents[] = $this->normalizer->normalize($documentToIndex, null, [
                'type' => 'webgriffe_sylius_elasticsearch_plugin',
                'channel' => $channel,
            ]);
        }

        return $documents;
    }

    public function getSettings(): array
    {
        $settings = [
            'analysis' => [
                'analyzer' => [
                    'store' => [
                        'type' => 'custom',
                        'tokenizer' => 'icu_tokenizer',
                        'char_filter' => ['html_strip'],
                        'filter' => ['lowercase', 'icu_folding', 'elision'],
                    ],
                ],
            ],
        ];
        $event = new ProductDocumentTypeSettingsEvent($settings);
        $this->eventDispatcher->dispatch($event);

        return $event->getSettings();
    }

    public function getMappings(): array
    {
        $mappings = [
            'properties' => [
                'sylius-id' => $this->keyword(false),
                'code' => $this->keyword(),
                'name-as-keyword' => $this->nestedTranslationKeywords(),
                'name' => $this->nestedTranslationTexts(),
                'enabled' => $this->boolean(),
                'description' => $this->nestedTranslationTexts(),
                'short-description' => $this->nestedTranslationTexts(),
                'slug' => $this->nestedTranslationTexts(),
                'meta-keywords' => $this->nestedTranslationTexts(),
                'meta-description' => $this->nestedTranslationTexts(),
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
                'translated-attributes' => [
                    'type' => 'nested',
                    'dynamic' => false,
                    'include_in_parent' => true,
                    'properties' => $this->attributeProperties(true),
                ],
                'product-options' => [
                    'type' => 'nested',
                    'dynamic' => false,
                    'include_in_parent' => true,
                    'properties' => $this->productOptionProperties(),
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
                'suggest' => [
                    'type' => 'completion',
                    'preserve_separators' => false,
                    'preserve_position_increments' => true,
                    'max_input_length' => 50,
                ],
            ],
        ];
        $event = new ProductDocumentTypeMappingsEvent($mappings);
        $this->eventDispatcher->dispatch($event);

        return $event->getMappings();
    }

    private function keyword(bool $index = true): array
    {
        return [
            'type' => 'keyword',
            'index' => $index,
        ];
    }

    private function text(bool $index = true): array
    {
        return [
            'type' => 'text',
            'index' => $index,
            'analyzer' => 'store',
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

    private function nestedTranslationKeywords(bool $indexValue = true): array
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

    private function nestedTranslationTexts(bool $indexValue = true): array
    {
        $locales = $this->localeRepository->findAll();
        $properties = [];
        foreach ($locales as $locale) {
            $localeCode = $locale->getCode();
            Assert::string($localeCode);
            $properties[$localeCode] = $this->text($indexValue);
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
            'name' => $this->nestedTranslationKeywords(),
        ];
    }

    private function attributeProperties(bool $translated = false): array
    {
        $locales = $this->localeRepository->findAll();
        $properties = [];
        foreach ($locales as $locale) {
            $localeCode = $locale->getCode();
            Assert::string($localeCode);
            $properties[$localeCode] = [
                'type' => 'nested',
                'dynamic' => false,
                'include_in_parent' => true,
                'properties' => $this->attributeValueProperties(),
            ];
        }
        if ($translated === false) {
            $properties = $this->attributeValueProperties();
        }

        return [
            'sylius-id' => $this->keyword(false),
            'code' => $this->keyword(),
            'type' => $this->keyword(false),
            'storage-type' => $this->keyword(false),
            'position' => $this->integer(false),
            'translatable' => $this->boolean(false),
            'filterable' => $this->boolean(true),
            'name' => $this->nestedTranslationKeywords(false),
            'values' => [
                'type' => 'nested',
                'dynamic' => false,
                'include_in_parent' => true,
                'properties' => $properties,
            ],
        ];
    }

    private function attributeValueProperties(): array
    {
        return [
            'sylius-id' => $this->keyword(false),
            'code' => $this->keyword(false),
            'locale' => $this->keyword(false),
            'values' => $this->keyword(),
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

    private function productOptionProperties(): array
    {
        $properties = $this->optionValueProperties();

        return [
            'sylius-id' => $this->keyword(false),
            'code' => $this->keyword(),
            'name' => $this->nestedTranslationKeywords(false),
            'position' => $this->integer(false),
            'filterable' => $this->boolean(),
            'values' => [
                'type' => 'nested',
                'dynamic' => false,
                'include_in_parent' => true,
                'properties' => $properties,
            ],
        ];
    }

    private function optionProperties(): array
    {
        return [
            'sylius-id' => $this->keyword(false),
            'code' => $this->keyword(),
            'name' => $this->nestedTranslationKeywords(false),
            'position' => $this->integer(false),
            'translatable' => $this->boolean(false),
            'filterable' => $this->boolean(),
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
            'code' => $this->keyword(),
            'value' => $this->keyword(false),
            'name' => $this->nestedTranslationKeywords(),
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
            'on-hand' => $this->integer(false),
            'on-hold' => $this->integer(false),
            'is-tracked' => $this->boolean(false),
            'shipping-required' => $this->boolean(false),
            'name' => $this->nestedTranslationTexts(),
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
            'label' => $this->nestedTranslationKeywords(),
            'description' => $this->nestedTranslationKeywords(),
        ];
    }
}
