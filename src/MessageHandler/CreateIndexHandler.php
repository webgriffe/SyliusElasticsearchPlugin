<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\MessageHandler;

use InvalidArgumentException;
use LRuozzi9\SyliusElasticsearchPlugin\Generator\IndexNameGeneratorInterface;
use LRuozzi9\SyliusElasticsearchPlugin\Manager\IndexManagerInterface;
use LRuozzi9\SyliusElasticsearchPlugin\Message\CreateIndex;
use LRuozzi9\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;

final readonly class CreateIndexHandler
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private IndexNameGeneratorInterface $indexNameGenerator,
        private IndexManagerInterface $indexManager,
        private DocumentTypeProviderInterface $documentTypeProvider,
    ) {
    }

    public function __invoke(CreateIndex $message): void
    {
        $channel = $this->channelRepository->find($message->getChannelId());
        if ($channel === null) {
            throw new InvalidArgumentException(sprintf(
                'Channel with id "%s" does not exist.',
                $message->getChannelId(),
            ));
        }

        $documentType = $this->documentTypeProvider->getDocumentType($message->getDocumentTypeCode());

        $indexName = $this->indexNameGenerator->generateName($channel, $documentType);
        $aliasName = $this->indexNameGenerator->generateAlias($channel, $documentType);
        $indexesToRemoveWildcard = $this->indexNameGenerator->generateWildcardPattern($channel, $documentType);

        $this->indexManager->create($indexName, $documentType);
        $this->indexManager->populate($indexName, $documentType);
        $this->indexManager->switchAlias($aliasName, $indexName);
        $this->indexManager->removeIndexes($indexesToRemoveWildcard, [$indexName]);
    }
}
