<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Model;

interface ResponseInterface
{
    public function getRouteName(): string;

    /**
     * @return array<string, string>
     */
    public function getRouteParams(): array;
}
