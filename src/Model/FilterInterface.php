<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

interface FilterInterface
{
    public function getValues(): array;

    public function getAttributeCode(): string;
}
