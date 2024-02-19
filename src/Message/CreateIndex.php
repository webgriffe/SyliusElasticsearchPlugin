<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Message;

final readonly class CreateIndex
{
    public function __construct(
        private string|int $channelId,
    ) {
    }

    public function getChannelId(): int|string
    {
        return $this->channelId;
    }
}
