<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Serializer;

use Psr\EventDispatcher\EventDispatcherInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentType\ProductDocumentTypeTaxonNormalizeEvent;
use Webmozart\Assert\Assert;

final readonly class TaxonNormalizer implements NormalizerInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param TaxonInterface|mixed $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $channel = $context['channel'];
        $taxon = $object;
        Assert::isInstanceOf($taxon, TaxonInterface::class);
        Assert::isInstanceOf($channel, ChannelInterface::class);

        $normalizedTaxon = [
            'sylius-id' => $taxon->getId(),
            'code' => $taxon->getCode(),
            'name' => [],
        ];
        /** @var TaxonTranslationInterface $taxonTranslation */
        foreach ($taxon->getTranslations() as $taxonTranslation) {
            $localeCode = $taxonTranslation->getLocale();
            Assert::string($localeCode);
            $normalizedTaxon['name'][] = [
                $localeCode => $taxonTranslation->getName(),
            ];
        }

        $event = new ProductDocumentTypeTaxonNormalizeEvent($taxon, $channel, $normalizedTaxon);
        $this->eventDispatcher->dispatch($event);

        return $event->getNormalizedTaxon();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [TaxonInterface::class => true];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TaxonInterface &&
            array_key_exists('type', $context) &&
            $context['type'] === 'webgriffe_sylius_elasticsearch_plugin'
        ;
    }
}
