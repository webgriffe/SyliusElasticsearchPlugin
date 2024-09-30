<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Serializer;

use Psr\EventDispatcher\EventDispatcherInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Product\Model\ProductOptionValueTranslationInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentType\ProductDocumentTypeProductOptionValueNormalizeEvent;
use Webmozart\Assert\Assert;

final class ProductOptionValueNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param ProductOptionValueInterface|mixed $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $channel = $context['channel'];
        $optionValue = $object;
        Assert::isInstanceOf($optionValue, ProductOptionValueInterface::class);
        Assert::isInstanceOf($channel, ChannelInterface::class);

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

        $event = new ProductDocumentTypeProductOptionValueNormalizeEvent($optionValue, $channel, $normalizedOptionValue);
        $this->eventDispatcher->dispatch($event);

        return $event->getNormalizedProductOptionValue();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ProductOptionValueInterface::class => true];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ProductOptionValueInterface &&
            array_key_exists('type', $context) &&
            $context['type'] === 'webgriffe_sylius_elasticsearch_plugin'
        ;
    }
}
