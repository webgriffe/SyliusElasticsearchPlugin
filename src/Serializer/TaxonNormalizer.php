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

/**
 * @final
 * @readonly
 */
class TaxonNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly NormalizerInterface $serializer,
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

        /** @var array<int|string> $alreadyNormalizedTaxons */
        $alreadyNormalizedTaxons = $context['already_normalized_taxons'] ?? [];

        $taxonId = $taxon->getId();
        if (!is_string($taxonId) && !is_int($taxonId)) {
            throw new \InvalidArgumentException('Taxon ID must be a string or an integer.');
        }
        if (in_array($taxonId, $alreadyNormalizedTaxons, true)) {
            return [
                'sylius-id' => $taxonId,
            ];
        }

        $alreadyNormalizedTaxons[] = $taxonId;
        $context['already_normalized_taxons'] = $alreadyNormalizedTaxons;

        $normalizedTaxon = [
            'sylius-id' => $taxonId,
            'code' => $taxon->getCode(),
            'enabled' => $taxon->isEnabled(),
            'left' => $taxon->getLeft(),
            'right' => $taxon->getRight(),
            'level' => $taxon->getLevel(),
            'position' => $taxon->getPosition(),
            'root' => null,
            'parent' => null,
            'children' => [],
            'name' => [],
            'slug' => [],
            'description' => [],
        ];
        $root = $taxon->getRoot();
        if ($root !== null) {
            $normalizedTaxon['root'] = $this->serializer->normalize($root, $format, $context);
        }
        $parent = $taxon->getParent();
        if ($parent !== null) {
            $normalizedTaxon['parent'] = $this->serializer->normalize($parent, $format, $context);
        }
        foreach ($taxon->getChildren() as $child) {
            $normalizedTaxon['children'][] = $this->serializer->normalize($child, $format, $context);
        }
        /** @var TaxonTranslationInterface $taxonTranslation */
        foreach ($taxon->getTranslations() as $taxonTranslation) {
            $localeCode = $taxonTranslation->getLocale();
            Assert::string($localeCode);
            $normalizedTaxon['name'][] = [
                $localeCode => $taxonTranslation->getName(),
            ];
            $normalizedTaxon['slug'][] = [
                $localeCode => $taxonTranslation->getSlug(),
            ];
            $normalizedTaxon['description'][] = [
                $localeCode => $taxonTranslation->getDescription(),
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
