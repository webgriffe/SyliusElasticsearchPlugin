<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\IndexManager\Output;

final readonly class Message implements MessageInterface
{
    private function __construct(
        private string $message,
    ) {
    }

    public static function createMessage(string $message): self
    {
        return new self($message);
    }

    public function __toString(): string
    {
        return $this->message;
    }
}
