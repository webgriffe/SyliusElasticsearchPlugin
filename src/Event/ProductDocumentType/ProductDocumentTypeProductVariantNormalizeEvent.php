<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentType;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ProductDocumentTypeProductVariantNormalizeEvent extends Event
{
    public function __construct(
        private readonly ProductVariantInterface $variant,
        private readonly ChannelInterface $channel,
        private array $normalizedProductVariant,
    ) {
    }

    public function getVariant(): ProductVariantInterface
    {
        return $this->variant;
    }

    public function getChannel(): ChannelInterface
    {
        return $this->channel;
    }

    public function getNormalizedProductVariant(): array
    {
        return $this->normalizedProductVariant;
    }

    public function setNormalizedProductVariant(array $normalizedProductVariant): void
    {
        $this->normalizedProductVariant = $normalizedProductVariant;
    }
}
