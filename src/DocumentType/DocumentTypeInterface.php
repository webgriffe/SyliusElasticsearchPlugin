<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\DocumentType;

use Sylius\Component\Core\Model\ChannelInterface;

interface DocumentTypeInterface
{
    public function getCode(): string;

    /**
     * @return array<array-key, mixed>
     */
    public function getDocuments(ChannelInterface $channel): array;

    /** @return array<string, array> */
    public function getMappings(): array;
}
