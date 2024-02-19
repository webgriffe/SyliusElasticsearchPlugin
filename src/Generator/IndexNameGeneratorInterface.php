<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Generator;

use Sylius\Component\Core\Model\ChannelInterface;

interface IndexNameGeneratorInterface
{
    public function generateName(ChannelInterface $channel): string;

    public function generateAlias(ChannelInterface $channel): string;
}
