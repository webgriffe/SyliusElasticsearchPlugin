<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\MessageHandler;

use InvalidArgumentException;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\Generator\IndexNameGeneratorInterface;
use Webgriffe\SyliusElasticsearchPlugin\Message\CreateIndex;
use Webgriffe\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;

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

        $this->indexManager->createIndex($indexName, $documentType->getMappings(), $documentType->getSettings());
        $this->indexManager->bulk($indexName, $documentType->getDocuments($channel));
        $this->indexManager->switchAlias($aliasName, $indexName);
        $this->indexManager->removeIndexes($indexesToRemoveWildcard, [$indexName]);
    }
}
