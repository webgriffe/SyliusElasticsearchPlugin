<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Provider;

use Webgriffe\SyliusElasticsearchPlugin\DocumentType\DocumentTypeInterface;

interface DocumentTypeProviderInterface
{
    /**
     * @return array<array-key, DocumentTypeInterface>
     */
    public function getDocumentsType(): array;

    public function getDocumentType(string $code): DocumentTypeInterface;
}
