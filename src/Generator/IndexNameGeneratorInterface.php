<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Generator;

use LRuozzi9\SyliusElasticsearchPlugin\DocumentType\DocumentTypeInterface;
use Sylius\Component\Core\Model\ChannelInterface;

interface IndexNameGeneratorInterface
{
    public function generateName(
        ChannelInterface $channel,
        DocumentTypeInterface $documentType,
    ): string;

    public function generateAlias(
        ChannelInterface $channel,
        DocumentTypeInterface $documentType,
    ): string;

    public function generateWildcardPattern(
        ChannelInterface $channel,
        DocumentTypeInterface $documentType,
    ): string;
}
