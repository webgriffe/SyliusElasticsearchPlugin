<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Model;

interface ResponseInterface
{
    public function getRouteName(): ?string;

    public function setRouteName(?string $routeName): void;

    /**
     * @return array<string, mixed>
     */
    public function getRouteParams(): ?array;

    /**
     * @param array<string, mixed> $routeParams
     */
    public function setRouteParams(?array $routeParams): void;
}
