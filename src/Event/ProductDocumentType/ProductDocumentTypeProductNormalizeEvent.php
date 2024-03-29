<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentType;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ProductDocumentTypeProductNormalizeEvent extends Event
{
    public function __construct(
        private readonly ProductInterface $product,
        private readonly ChannelInterface $channel,
        private array $normalizedProduct,
    ) {
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    public function getChannel(): ChannelInterface
    {
        return $this->channel;
    }

    public function getNormalizedProduct(): array
    {
        return $this->normalizedProduct;
    }

    public function setNormalizedProduct(array $normalizedProduct): void
    {
        $this->normalizedProduct = $normalizedProduct;
    }
}
