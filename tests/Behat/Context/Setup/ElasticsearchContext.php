<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webgriffe\SyliusElasticsearchPlugin\Message\CreateIndex;
use Webgriffe\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;

final readonly class ElasticsearchContext implements Context
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private DocumentTypeProviderInterface $documentTypeProvider,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @Given the store is indexed on Elasticsearch
     */
    public function theStoreIsIndexedOnElasticsearch(): void
    {
        foreach ($this->channelRepository->findAll() as $channel) {
            foreach ($this->documentTypeProvider->getDocumentsType() as $documentType) {
                $this->messageBus->dispatch(new CreateIndex($channel->getId(), $documentType->getCode()));
            }
        }

        sleep(1); // Wait for the indexing to be completed, TODO: find a way to query the client for the indexing status
    }
}
