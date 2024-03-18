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

    /**
     * @param FactoryInterface<ProductImageInterface> $productImageFactory
     * @param FactoryInterface<ChannelPricingInterface> $channelPricingFactory
     * @param FactoryInterface<CatalogPromotionInterface> $catalogPromotionFactory
     */
    public function __construct(
        private readonly ProductResponseFactoryInterface $productResponseFactory,
        private readonly LocaleContextInterface $localeContext,
        private readonly ChannelContextInterface $channelContext,
        private readonly FactoryInterface $productImageFactory,
        private readonly ProductVariantFactoryInterface $productVariantFactory,
        private readonly FactoryInterface $channelPricingFactory,
        private readonly FactoryInterface $catalogPromotionFactory,
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
        /** @var array{sylius-id: int, code: string, name: LocalizedField, description: LocalizedField, short-description: LocalizedField, taxons: array, main_taxon: array, slug: LocalizedField, images: array, variants: array} $source */
        $source = $document['_source'];
        $localeCode = $this->localeContext->getLocaleCode();
        $productResponse = $this->productResponseFactory->createNew();
        $productResponse->setCode($source['code']);
        $productResponse->setCurrentLocale($localeCode);
        $productResponse->setName($this->getValueFromLocalizedField($source['name'], $localeCode));
        $productResponse->setSlug($this->getSlug($source['slug'], $localeCode));
        $productResponse->setDescription($this->getValueFromLocalizedField($source['description'], $localeCode));
        $productResponse->setShortDescription($this->getValueFromLocalizedField($source['short-description'], $localeCode));

        /** @var array{path: ?string, type: ?string} $esImage */
        foreach ($source['images'] as $esImage) {
            $productImage = $this->productImageFactory->createNew();
            $productImage->setPath($esImage['path']);
            $productImage->setType($esImage['type']);
            $productResponse->addImage($productImage);
        }

        // Doing this sorting here could avoid to make any DB query to get the variants in the right order just for
        // getting the "default variant"
        $sortedVariants = $source['variants'];
        usort(
            $sortedVariants,
            static function (array $a, array $b): int {
                return $a['position'] <=> $b['position'];
            },
        );
        /** @var array{code: ?string, enabled: ?bool, position: int, price: array{price: ?int, original-price: ?int, applied-promotions: array}} $esVariant */
        foreach ($sortedVariants as $esVariant) {
            $productVariant = $this->productVariantFactory->createForProduct($productResponse);
            Assert::isInstanceOf($productVariant, ProductVariantInterface::class);
            $productVariant->setCode($esVariant['code']);
            $productVariant->setEnabled($esVariant['enabled']);
            $productVariant->setPosition($esVariant['position']);

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

            $productResponse->addVariant($productVariant);
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
