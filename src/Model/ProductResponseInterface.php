<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

interface ProductResponseInterface extends ResponseInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getImage(): string;

    public function getPrice(): int;

    public function getOriginalPrice(): int;
}
