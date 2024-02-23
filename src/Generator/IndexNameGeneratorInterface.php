<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Generator;

use Webgriffe\SyliusElasticsearchPlugin\DocumentType\DocumentTypeInterface;
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
