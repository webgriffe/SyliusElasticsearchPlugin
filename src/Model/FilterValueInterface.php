<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

interface FilterValueInterface
{
    public function getKey(): string;

    public function getLabel(): string;

    public function getOccurrences(): int;
}
