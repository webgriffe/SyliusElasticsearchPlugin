<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

final readonly class FilterValue implements FilterValueInterface
{
    public function __construct(
        private string $key,
        private string $label,
        private int $occurrences,
    ) {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getOccurrences(): int
    {
        return $this->occurrences;
    }
}
