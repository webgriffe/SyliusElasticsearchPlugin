<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Generator;

use DateTimeImmutable;
use DateTimeZone;
use Sylius\Component\Core\Model\ChannelInterface;

final readonly class IndexNameGenerator implements IndexNameGeneratorInterface
{
    public function __construct(private string $indexPattern)
    {
    }

    public function generateName(ChannelInterface $channel): string
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $replacement = [
            '<CHANNEL>' => strtolower($channel->getCode()),
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
        return strtolower($channel->getCode());
    }
}
