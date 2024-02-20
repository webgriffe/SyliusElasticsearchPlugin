<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\DocumentType;

use LRuozzi9\SyliusElasticsearchPlugin\Model\DocumentableInterface;

interface DocumentTypeInterface
{
    public function getCode(): string;

    /**
     * @return array<array-key, mixed>
     */
    public function getDocuments(): array;

    /** @return array<string, array> */
    public function getMappings(): array;
}
