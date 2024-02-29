<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Model;

interface FilterInterface
{
    public function getType(): string;

    public function getName(): string;

    /**
     * @return FilterValueInterface[]
     */
    public function getValues(): array;

    public function getKeyCode(): string;

    public function getQueryStringKey(): string;

    public static function resolveFromRawData(string $aggregationKey, array $rawData): self;
}
