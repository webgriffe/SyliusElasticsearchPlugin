<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\MessageHandler;

use InvalidArgumentException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\Exception\CreateIndexException;
use LRuozzi9\SyliusElasticsearchPlugin\Generator\IndexNameGeneratorInterface;
use LRuozzi9\SyliusElasticsearchPlugin\Manager\IndexManagerInterface;
use LRuozzi9\SyliusElasticsearchPlugin\Message\CreateIndex;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;

final readonly class CreateIndexHandler
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private IndexNameGeneratorInterface $indexNameGenerator,
        private IndexManagerInterface $indexManager,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws CreateIndexException
     */
    public function __invoke(CreateIndex $message): void
    {
        $channel = $this->channelRepository->find($message->getChannelId());
        if ($channel === null) {
            throw new InvalidArgumentException(sprintf(
                'Channel with id "%s" does not exist.',
                $message->getChannelId(),
            ));
        }
        $indexName = $this->indexNameGenerator->generateName($channel);
        $aliasName = $this->indexNameGenerator->generateAlias($channel);
        $indexesToRemoveWildcard = $this->indexNameGenerator->generateWildcardPattern($channel);

        $this->indexManager->create($indexName, []);
        $this->indexManager->switchAlias($aliasName, $indexName);
        $this->indexManager->removeIndexes($indexesToRemoveWildcard, [$indexName]);
    }
}
