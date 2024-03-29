<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Parser;

use RuntimeException;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\CatalogPromotionInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webgriffe\SyliusElasticsearchPlugin\Factory\ProductResponseFactoryInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\ProductResponseInterface;
use Webmozart\Assert\Assert;

/**
 * @psalm-type LocalizedField = array<array-key, array<string, string>>
 */
final class ElasticsearchProductDocumentParser implements DocumentParserInterface
{
    private ?string $defaultLocale = null;

    /** @var array<string|int, ProductVariantInterface> */
    private array $productVariants = [];

    /** @var array<string|int, ProductOptionValueInterface> */
    private array $productOptionValues = [];

    /**
     * @param FactoryInterface<ProductImageInterface> $productImageFactory
     * @param FactoryInterface<ChannelPricingInterface> $channelPricingFactory
     * @param FactoryInterface<CatalogPromotionInterface> $catalogPromotionFactory
     * @param FactoryInterface<ProductOptionInterface> $productOptionFactory
     * @param FactoryInterface<ProductOptionValueInterface> $productOptionValueFactory
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
        private readonly string $fallbackLocaleCode,
    ) {
    }

    public function parse(array $document): ProductResponseInterface
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();
        $defaultLocale = $channel->getDefaultLocale();
        $this->defaultLocale = $this->fallbackLocaleCode;
        if ($defaultLocale instanceof LocaleInterface) {
            $defaultLocaleCode = $defaultLocale->getCode();
            if ($defaultLocaleCode !== null) {
                $this->defaultLocale = $defaultLocaleCode;
            }
        }
        /** @var array{sylius-id: int, code: string, name: LocalizedField, description: LocalizedField, short-description: LocalizedField, taxons: array, main_taxon: array, slug: LocalizedField, images: array, variants: array, product-options: array} $source */
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

                $productOption->addValue($productOptionValue);
            }

            $productResponse->addOption($productOption);
        }

        // Doing this sorting here could avoid to make any DB query to get the variants in the right order just for
        // getting the "default variant"
        /** @var array<array-key, array{sylius-id: int|string, code: ?string, enabled: ?bool, position: int, price: array{price: ?int, original-price: ?int, applied-promotions: array}, options: array}> $sortedVariants */
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
            $productResponse->addImage($productImage);
        }

        return $productResponse;
    }

    /**
     * @param LocalizedField $localizedField
     */
    private function getValueFromLocalizedField(array $localizedField, string $localeCode): ?string
    {
        $fallbackValue = null;
        $defaultLocale = $this->defaultLocale;
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
}
