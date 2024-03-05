<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentType;

use Symfony\Contracts\EventDispatcher\Event;

final class ProductDocumentTypeMappingsEvent extends Event
{
    /**
     * @param array<string, array> $mappings
     */
    public function __construct(private array $mappings)
    {
    }

    /**
     * @return array<string, array>
     */
    public function getMappings(): array
    {
        return $this->mappings;
    }

    /**
     * @param array<string, array> $mappings
     */
    public function setMappings(array $mappings): void
    {
        $this->mappings = $mappings;
    }
}
