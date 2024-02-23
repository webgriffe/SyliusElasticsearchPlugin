<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Generator;

use Sylius\Component\Core\Model\ChannelInterface;
use Webgriffe\SyliusElasticsearchPlugin\DocumentType\DocumentTypeInterface;

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
