<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Repository;

use Sylius\Component\Core\Model\ChannelInterface;

interface DocumentTypeRepositoryInterface
{
    /**
     * @return object[]
     */
    public function findDocumentsToIndex(ChannelInterface $channel): array;

    public function findDocumentToIndex(string|int $identifier, ChannelInterface $channel): ?object;
}
