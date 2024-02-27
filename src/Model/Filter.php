<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

final class Filter implements FilterInterface
{
    public function __construct(
        private string $attributeCode,
        private array $values,
    ) {
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
