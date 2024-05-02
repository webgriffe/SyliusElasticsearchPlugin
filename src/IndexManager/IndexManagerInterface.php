<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\IndexManager;

use Generator;
use Sylius\Component\Core\Model\ChannelInterface;
use Webgriffe\SyliusElasticsearchPlugin\DocumentType\DocumentTypeInterface;
use Webgriffe\SyliusElasticsearchPlugin\IndexManager\Output\MessageInterface;

interface IndexManagerInterface
{
    /**
     * @return Generator<array-key, MessageInterface>
     */
    public function create(ChannelInterface $channel, DocumentTypeInterface $documentType): Generator;

    /**
     * @return Generator<array-key, MessageInterface>
     */
    public function upsertDocuments(
        ChannelInterface $channel,
        DocumentTypeInterface $documentType,
        string|int ...$identifiers,
    ): Generator;

    /**
     * @return Generator<array-key, MessageInterface>
     */
    public function removeDocuments(
        ChannelInterface $channel,
        DocumentTypeInterface $documentType,
        int|string ...$identifiers,
    ): Generator;
}
