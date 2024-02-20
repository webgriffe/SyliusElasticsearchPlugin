<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Model;

interface ProductResponseInterface extends ResponseInterface
{
    public function getImage(): string;

    public function getName(): string;

    public function getPrice(): int;

    public function getOriginalPrice(): int;
}
