<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;

interface ProductResponseInterface extends ResponseInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getPrice(): int;

    public function getOriginalPrice(): int;

    public function getImages(): Collection;

    public function getImagesByType(string $type): Collection;

    public function setImages(array $images): void;

    /**
     * @return Collection<array-key, ProductVariantResponseInterface>
     */
    public function getVariants(): Collection;

    /**
     * @return Collection<array-key, ProductVariantResponseInterface>
     */
    public function getEnabledVariants(): Collection;

    /**
     * @param Collection<array-key, ProductVariantResponseInterface> $variants
     */
    public function setVariants(Collection $variants): void;

    public function getSlug(): ?string;

    public function setSlug(?string $slug): void;

    public function getTranslation(): ?ProductTranslationInterface;

    public function setTranslation(?ProductTranslationInterface $translation): void;
}
