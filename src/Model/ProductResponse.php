<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ProductTranslationInterface;

final class ProductResponse extends AbstractResponse implements ProductResponseInterface
{
    private ?string $name = null;

    private array $images = [];

    /**
     * @var Collection<array-key, ProductVariantResponseInterface>
     */
    private Collection $variants;

    private ?string $slug = null;

    private ?ProductTranslationInterface $translation = null;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPrice(): int
    {
        return 0;
    }

    public function getOriginalPrice(): int
    {
        return 0;
    }

    public function getImages(): Collection
    {
        return new ArrayCollection($this->images);
    }

    public function getImagesByType(string $type): Collection
    {
        return $this->getImages()->filter(function (array $image) use ($type): bool {
            return $type === $image['type'];
        });
    }

    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function getEnabledVariants(): Collection
    {
        return $this->getVariants()->filter(function (ProductVariantResponseInterface $variant): bool {
            return $variant->isEnabled();
        });
    }

    public function setVariants(Collection $variants): void
    {
        $this->variants = $variants;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getTranslation(): ?ProductTranslationInterface
    {
        return $this->translation;
    }

    public function setTranslation(?ProductTranslationInterface $translation): void
    {
        $this->translation = $translation;
    }
}
