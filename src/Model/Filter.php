<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

final readonly class Filter implements FilterInterface
{
    public function __construct(
        private string $keyCode,
        private string $name,
        private string $type,
        private array $values,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function getKeyCode(): string
    {
        return $this->keyCode;
    }

    public function getQueryStringKey(): string
    {
        return sprintf('filters[%s][%s]', $this->getType(), $this->getKeyCode());
    }
}
