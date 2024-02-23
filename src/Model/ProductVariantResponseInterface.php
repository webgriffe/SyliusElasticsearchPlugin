<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\CatalogPromotionInterface;
use Sylius\Component\Core\Model\ChannelInterface;

interface ProductVariantResponseInterface
{
    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    public function getPosition(): ?int;

    public function setPosition(?int $position): void;

    public function getPrice(): ?int;

    public function setPrice(?int $price): void;

    public function getOriginalPrice(): ?int;

    public function setOriginalPrice(?int $originalPrice): void;

    /**
     * @return Collection<array-key, CatalogPromotionInterface>
     */
    public function getAppliedPromotionsForChannel(ChannelInterface $channel): Collection;

    /**
     * @param Collection<array-key, CatalogPromotionInterface> $appliedPromotions
     * @return void
     */
    public function setAppliedPromotionsForChannel(Collection $appliedPromotions): void;
}
