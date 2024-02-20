<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Model;

final class ProductResponse implements ProductResponseInterface
{
    public function __construct(
        private ?string $routeName = null,
        private ?array $routeParams = [],
    ) {
    }

    public function getImage(): string
    {
        // TODO: Implement getImage() method.
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function getPrice(): int
    {
        // TODO: Implement getPrice() method.
    }

    public function getOriginalPrice(): int
    {
        // TODO: Implement getOriginalPrice() method.
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }
}
