<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Model;

final class ProductResponse implements ProductResponseInterface
{
    /**
     * @param array<string, string>|null $routeParams
     */
    public function __construct(
        private ?string $routeName = null,
        private ?array $routeParams = [],
    ) {
    }

    public function getImage(): string
    {
        return 'TODO';
    }

    public function getName(): string
    {
        return 'TODO';
    }

    public function getPrice(): int
    {
        return 0;
    }

    public function getOriginalPrice(): int
    {
        return 0;
    }

    public function getRouteName(): string
    {
        return (string) $this->routeName;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams ?? [];
    }
}
