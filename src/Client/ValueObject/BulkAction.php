<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Client\ValueObject;

use Webgriffe\SyliusElasticsearchPlugin\Client\Enum\Action;

final readonly class BulkAction
{
    public function __construct(
        private Action $action,
        private string $index,
        private ?array $payload = null,
        private ?string $type = null,
        private null|string|int $id = null,
    ) {
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function getId(): int|string|null
    {
        return $this->id;
    }
}
