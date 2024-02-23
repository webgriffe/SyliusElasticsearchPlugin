<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Generator;

use DateTimeImmutable;
use DateTimeZone;
use Webgriffe\SyliusElasticsearchPlugin\DocumentType\DocumentTypeInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Webmozart\Assert\Assert;

final readonly class IndexNameGenerator implements IndexNameGeneratorInterface
{
    public function __construct(private string $indexPattern)
    {
    }

    public function generateName(
        ChannelInterface $channel,
        DocumentTypeInterface $documentType,
    ): string {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $replacement = [
            '<CHANNEL>' => $this->getChannelCodeInLowerCase($channel),
            '<DOCUMENT_TYPE>' => $this->getDocumentTypeCodeInLowerCase($documentType),
            '<DATE>' => $now->format('Ymd'),
            '<TIME>' => $now->format('His'),
        ];

        return str_replace(
            array_keys($replacement),
            array_values($replacement),
            $this->indexPattern,
        );
    }

    public function generateAlias(
        ChannelInterface $channel,
        DocumentTypeInterface $documentType,
    ): string {
        return sprintf(
            '%s_%s',
            $this->getChannelCodeInLowerCase($channel),
            $this->getDocumentTypeCodeInLowerCase($documentType),
        );
    }

    public function generateWildcardPattern(
        ChannelInterface $channel,
        DocumentTypeInterface $documentType,
    ): string {
        $replacement = [
            '<CHANNEL>' => $this->getChannelCodeInLowerCase($channel),
            '<DOCUMENT_TYPE>' => $this->getDocumentTypeCodeInLowerCase($documentType),
            '<DATE>' => '*',
            '<TIME>' => '*',
        ];

        return str_replace(
            array_keys($replacement),
            array_values($replacement),
            $this->indexPattern,
        );
    }

    private function getChannelCodeInLowerCase(ChannelInterface $channel): string
    {
        $channelCode = $channel->getCode();
        Assert::stringNotEmpty($channelCode);

        return strtolower($channelCode);
    }

    private function getDocumentTypeCodeInLowerCase(DocumentTypeInterface $documentType): string
    {
        return strtolower($documentType->getCode());
    }
}
