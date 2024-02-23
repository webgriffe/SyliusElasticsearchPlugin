<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

interface ResponseInterface
{
    public function getRouteName(): string;

    /**
     * @return array<string, mixed>
     */
    public function getRouteParams(): array;
}
