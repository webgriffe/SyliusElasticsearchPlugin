<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\DocumentType;

final class ProductDocumentType implements DocumentTypeInterface
{
    public function getCode(): string
    {
        return 'product';
    }

    public function getDocuments(): array
    {
        return [
            0 => [
                'code' => 'product_1',
                'name' => 'Product 1',
                'description' => 'Description of product 1',
            ],
        ];
    }

    public function getMappings(): array
    {
        return [
            'properties' => [
                'code' => [
                    'type' => 'keyword',
                ],
                'name' => [
                    'type' => 'text',
                ],
                'description' => [
                    'type' => 'text',
                ],
            ]
        ];
    }
}
