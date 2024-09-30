<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentType;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ProductDocumentTypeProductOptionValueNormalizeEvent extends Event
{
    public function __construct(
        private readonly ProductOptionValueInterface $optionValue,
        private readonly ChannelInterface $channel,
        private array $normalizedProductOptionValue,
    ) {
    }

    public function getOptionValue(): ProductOptionValueInterface
    {
        return $this->optionValue;
    }

    public function getChannel(): ChannelInterface
    {
        return $this->channel;
    }

    public function getNormalizedProductOptionValue(): array
    {
        return $this->normalizedProductOptionValue;
    }

    public function setNormalizedProductOptionValue(array $normalizedProductOptionValue): void
    {
        $this->normalizedProductOptionValue = $normalizedProductOptionValue;
    }
}
