<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Provider;

use LRuozzi9\SyliusElasticsearchPlugin\DocumentType\DocumentTypeInterface;

interface DocumentTypeProviderInterface
{
    /**
     * @return array<array-key, DocumentTypeInterface>
     */
    public function getDocumentsType(): array;

    public function getDocumentType(string $code): DocumentTypeInterface;
}
