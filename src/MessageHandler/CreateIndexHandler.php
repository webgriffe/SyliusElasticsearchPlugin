<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\MessageHandler;

use InvalidArgumentException;
use LRuozzi9\SyliusElasticsearchPlugin\Client\ClientInterface;
use LRuozzi9\SyliusElasticsearchPlugin\Generator\IndexNameGeneratorInterface;
use LRuozzi9\SyliusElasticsearchPlugin\Message\CreateIndex;
use LRuozzi9\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;

final readonly class CreateIndexHandler
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private IndexNameGeneratorInterface $indexNameGenerator,
        private ClientInterface $indexManager,
        private DocumentTypeProviderInterface $documentTypeProvider,
    ) {
    }

    public function __invoke(CreateIndex $message): void
    {
        $channel = $this->channelRepository->find($message->getChannelId());
        if (!$channel instanceof ChannelInterface) {
            throw new InvalidArgumentException(sprintf(
                'Channel with id "%s" does not exist.',
                $message->getChannelId(),
            ));
        }

        $documentType = $this->documentTypeProvider->getDocumentType($message->getDocumentTypeCode());

        $indexName = $this->indexNameGenerator->generateName($channel, $documentType);
        $aliasName = $this->indexNameGenerator->generateAlias($channel, $documentType);
        $indexesToRemoveWildcard = $this->indexNameGenerator->generateWildcardPattern($channel, $documentType);

        $this->indexManager->createIndex($indexName, $documentType->getMappings());
        $this->indexManager->bulk($indexName, $documentType->getDocuments());
        $this->indexManager->switchAlias($aliasName, $indexName);
        $this->indexManager->removeIndexes($indexesToRemoveWildcard, [$indexName]);
    }
}
