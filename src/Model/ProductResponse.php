<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

final class ProductResponse extends AbstractResponse implements ProductResponseInterface
{
    private ?string $name = null;

    public function getImage(): string
    {
        return 'TODO';
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
}
