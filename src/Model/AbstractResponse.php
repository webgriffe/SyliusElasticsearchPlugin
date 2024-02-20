<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Model;

abstract class AbstractResponse implements ResponseInterface
{
    private ?string $routeName = null;

    /** @var array<string, mixed>|null */
    private ?array $routeParams = null;

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(?string $routeName): void
    {
        $this->routeName = $routeName;
    }

    public function getRouteParams(): ?array
    {
        return $this->routeParams;
    }

    public function setRouteParams(?array $routeParams): void
    {
        $this->routeParams = $routeParams;
    }
}
