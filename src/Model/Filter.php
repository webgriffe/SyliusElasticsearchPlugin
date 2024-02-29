<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

abstract readonly class Filter implements FilterInterface
{
    /**
     * @param FilterValueInterface[] $values
     */
    public function __construct(
        private string $keyCode,
        private string $name,
        private array $values,
    ) {
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
