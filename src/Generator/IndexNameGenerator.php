<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Generator;

use DateTimeImmutable;
use DateTimeZone;
use Sylius\Component\Core\Model\ChannelInterface;
use Webmozart\Assert\Assert;

final readonly class IndexNameGenerator implements IndexNameGeneratorInterface
{
    public function __construct(private string $indexPattern)
    {
    }

    public function generateName(ChannelInterface $channel): string
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $replacement = [
            '<CHANNEL>' => $this->getChannelCodeInLowerCase($channel),
            '<DATE>' => $now->format('Ymd'),
            '<TIME>' => $now->format('His'),
        ];

        return str_replace(
            array_keys($replacement),
            array_values($replacement),
            $this->indexPattern
        );
    }

    public function generateAlias(ChannelInterface $channel): string
    {
        return $this->getChannelCodeInLowerCase($channel);
    }

    public function generateWildcardPattern(ChannelInterface $channel): string
    {
        $replacement = [
            '<CHANNEL>' => $this->getChannelCodeInLowerCase($channel),
            '<DATE>' => '*',
            '<TIME>' => '*',
        ];

        return str_replace(
            array_keys($replacement),
            array_values($replacement),
            $this->indexPattern
        );
    }

    private function getChannelCodeInLowerCase(ChannelInterface $channel): string
    {
        $channelCode = $channel->getCode();
        Assert::stringNotEmpty($channelCode);

        return strtolower($channelCode);
    }
}
