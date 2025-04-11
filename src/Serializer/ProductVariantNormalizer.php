<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Serializer;

use Psr\EventDispatcher\EventDispatcherInterface;
use Sylius\Component\Core\Model\CatalogPromotionInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionTranslationInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;
use Sylius\Component\Promotion\Model\CatalogPromotionTranslationInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentType\ProductDocumentTypeProductVariantNormalizeEvent;
use Webgriffe\SyliusElasticsearchPlugin\Model\FilterableInterface;
use Webmozart\Assert\Assert;

/**
 * @final
 */
class ProductVariantNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly NormalizerInterface $serializer,
    ) {
    }

    /**
     * @param ProductVariantInterface|mixed $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $channel = $context['channel'];
        $variant = $object;
        Assert::isInstanceOf($variant, ProductVariantInterface::class);
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $normalizedVariant = [
            'sylius-id' => $variant->getId(),
            'code' => $variant->getCode(),
            'enabled' => $variant->isEnabled(),
            'position' => $variant->getPosition(),
            'weight' => $variant->getWeight(),
            'width' => $variant->getWidth(),
            'height' => $variant->getHeight(),
            'depth' => $variant->getDepth(),
            'shipping-required' => $variant->isShippingRequired(),
            'name' => [],
            'on-hand' => $variant->getOnHand(),
            'on-hold' => $variant->getOnHold(),
            'is-tracked' => $variant->isTracked(),
            'price' => $this->normalizeChannelPricing($variant->getChannelPricingForChannel($channel)),
            'options' => [],
            'created-at' => $variant->getCreatedAt()?->format('c'),
        ];
        /** @var array<array-key, array{option: ProductOptionInterface, value: ProductOptionValueInterface}> $variantOptionsWithValue */
        $variantOptionsWithValue = [];
        foreach ($variant->getOptionValues() as $optionValue) {
            $option = $optionValue->getOption();
            Assert::isInstanceOf($option, ProductOptionInterface::class);
            $optionId = $option->getId();
            if (!is_string($optionId) && !is_int($optionId)) {
                throw new \RuntimeException('Option ID different from string or integer is not supported.');
            }
            if (array_key_exists($optionId, $variantOptionsWithValue)) {
                throw new \RuntimeException('Multiple values for the same option are not supported.');
            }
            $variantOptionsWithValue[$optionId] = [
                'option' => $option,
                'value' => $optionValue,
            ];
        }
        foreach ($variantOptionsWithValue as $optionAndValue) {
            $normalizedVariant['options'][] = $this->normalizeProductOptionAndProductOptionValue(
                $optionAndValue['option'],
                $optionAndValue['value'],
                $format,
                $context,
            );
        }

        /** @var ProductVariantTranslationInterface $variantTranslation */
        foreach ($variant->getTranslations() as $variantTranslation) {
            $localeCode = $variantTranslation->getLocale();
            Assert::string($localeCode);
            $normalizedVariant['name'][] = [
                $localeCode => $variantTranslation->getName(),
            ];
        }

        $event = new ProductDocumentTypeProductVariantNormalizeEvent($variant, $channel, $normalizedVariant);
        $this->eventDispatcher->dispatch($event);

        return $event->getNormalizedProductVariant();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ProductVariantInterface::class => true];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ProductVariantInterface &&
            array_key_exists('type', $context) &&
            $context['type'] === 'webgriffe_sylius_elasticsearch_plugin'
        ;
    }

    private function normalizeChannelPricing(?ChannelPricingInterface $channelPricing): ?array
    {
        if ($channelPricing === null) {
            return null;
        }
        $normalizedChannelPricing = [
            'price' => $channelPricing->getPrice(),
            'original-price' => $channelPricing->getOriginalPrice(),
            'minimum-price' => $channelPricing->getMinimumPrice(),
            'lowest-price-before-discount' => null,
            'applied-promotions' => [],
        ];
        // @phpstan-ignore-next-line
        if (method_exists($channelPricing, 'getLowestPriceBeforeDiscount')) {
            $normalizedChannelPricing['lowest-price-before-discount'] = $channelPricing->getLowestPriceBeforeDiscount();
        }
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
                $localeCode = $catalogPromotionTranslation->getLocale();
                Assert::string($localeCode);
                $normalizedCatalogPromotion['label'][] = [
                    $localeCode => $catalogPromotionTranslation->getLabel(),
                ];
                $normalizedCatalogPromotion['description'][] = [
                    $localeCode => $catalogPromotionTranslation->getDescription(),
                ];
            }

            $normalizedChannelPricing['applied-promotions'][] = $normalizedCatalogPromotion;
        }

        return $normalizedChannelPricing;
    }

    private function normalizeProductOptionAndProductOptionValue(
        ProductOptionInterface $option,
        ProductOptionValueInterface $optionValue,
        ?string $format = null,
        array $context = [],
    ): array {
        $filterable = false;
        if ($option instanceof FilterableInterface) {
            $filterable = $option->isFilterable();
        }
        $normalizedOption = [
            'sylius-id' => $option->getId(),
            'code' => $option->getCode(),
            'name' => [],
            'filterable' => $filterable,
            'value' => $this->serializer->normalize($optionValue, $format, $context),
        ];
        /** @var ProductOptionTranslationInterface $optionTranslation */
        foreach ($option->getTranslations() as $optionTranslation) {
            $localeCode = $optionTranslation->getLocale();
            Assert::string($localeCode);
            $normalizedOption['name'][] = [
                $localeCode => $optionTranslation->getName(),
            ];
        }

        return $normalizedOption;
    }
}
