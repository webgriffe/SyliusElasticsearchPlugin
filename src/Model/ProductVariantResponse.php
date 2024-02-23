<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ChannelInterface;

final class ProductVariantResponse implements ProductVariantResponseInterface
{
    private bool $enabled = true;
    private ?int $position = null;
    private ?int $price = null;
    private ?int $originalPrice = null;
    private Collection $appliedPromotions;

    public function __construct()
    {
        $this->appliedPromotions = new ArrayCollection();
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): void
    {
        $this->price = $price;
    }

    public function getOriginalPrice(): ?int
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(?int $originalPrice): void
    {
        $this->originalPrice = $originalPrice;
    }

    public function getAppliedPromotionsForChannel(ChannelInterface $channel): Collection
    {
        return $this->appliedPromotions;
    }

    public function setAppliedPromotionsForChannel(Collection $appliedPromotions): void
    {
        $this->appliedPromotions = $appliedPromotions;
    }
}
