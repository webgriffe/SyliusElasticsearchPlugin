<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Message;

final readonly class CreateIndex
{
    public function __construct(
        private string|int $channelId,
        private string $documentTypeCode,
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
}
