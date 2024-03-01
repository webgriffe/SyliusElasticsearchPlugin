<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Repository;

interface DocumentTypeRepositoryInterface
{
    /**
     * @return object[]
     */
    public function findDocumentsToIndex(): array;
}
