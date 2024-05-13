<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\MessageHandler;

use InvalidArgumentException;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Webgriffe\SyliusElasticsearchPlugin\IndexManager\IndexManagerInterface;
use Webgriffe\SyliusElasticsearchPlugin\Message\CreateIndex;
use Webgriffe\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;

final readonly class CreateIndexHandler
{
    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     */
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private DocumentTypeProviderInterface $documentTypeProvider,
        private IndexManagerInterface $indexManager,
    ) {
    }

    /**
     * @psalm-suppress UnusedForeachValue
     */
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

        foreach ($this->indexManager->create($channel, $documentType) as $outputMessage) {
        }
    }
}
