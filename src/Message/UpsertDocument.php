<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Message;

final readonly class UpsertDocument
{
    public function __construct(
        private string|int $channelId,
        private string $documentTypeCode,
        private string|int $documentIdentifier,
    ) {
    }

    public function getChannelId(): int|string
    {
        return $this->channelId;
    }

    public function getDocumentTypeCode(): string
    {
        return $this->documentTypeCode;
    }

    public function getDocumentIdentifier(): int|string
    {
        return $this->documentIdentifier;
    }
}
