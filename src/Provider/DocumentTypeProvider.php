<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Provider;

use InvalidArgumentException;
use Webgriffe\SyliusElasticsearchPlugin\DocumentType\DocumentTypeInterface;

final class DocumentTypeProvider implements DocumentTypeProviderInterface
{
    /** @var DocumentTypeInterface[] */
    private array $documentTypes = [];

    public function addDocumentType(DocumentTypeInterface $documentType): void
    {
        $this->documentTypes[] = $documentType;
    }

    public function getDocumentsType(): array
    {
        return $this->documentTypes;
    }

    public function getDocumentType(string $code): DocumentTypeInterface
    {
        foreach ($this->documentTypes as $documentType) {
            if ($documentType->getCode() === $code) {
                return $documentType;
            }
        }

        throw new InvalidArgumentException(sprintf('Document type with code "%s" does not exist.', $code));
    }
}
