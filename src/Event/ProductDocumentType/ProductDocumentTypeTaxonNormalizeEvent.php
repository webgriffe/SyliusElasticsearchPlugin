<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentType;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ProductDocumentTypeTaxonNormalizeEvent extends Event
{
    public function __construct(
        private readonly TaxonInterface $taxon,
        private readonly ChannelInterface $channel,
        private array $normalizedTaxon,
    ) {
    }

    public function getTaxon(): TaxonInterface
    {
        return $this->taxon;
    }

    public function getChannel(): ChannelInterface
    {
        return $this->channel;
    }

    public function getNormalizedTaxon(): array
    {
        return $this->normalizedTaxon;
    }

    public function setNormalizedTaxon(array $normalizedTaxon): void
    {
        $this->normalizedTaxon = $normalizedTaxon;
    }
}
