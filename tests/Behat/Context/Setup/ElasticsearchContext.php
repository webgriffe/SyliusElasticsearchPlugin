<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Webgriffe\SyliusElasticsearchPlugin\IndexManager\IndexManagerInterface;
use Webgriffe\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;

final readonly class ElasticsearchContext implements Context
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
     * @Given the store is indexed on Elasticsearch
     */
    public function theStoreIsIndexedOnElasticsearch(): void
    {
        /** @var ChannelInterface $channel */
        foreach ($this->channelRepository->findAll() as $channel) {
            foreach ($this->documentTypeProvider->getDocumentsType() as $documentType) {
                foreach ($this->indexManager->create($channel, $documentType) as $message) {
                    // Just for cycling the generator
                }
            }
        }

        sleep(1); // Wait for the indexing to be completed, TODO: find a way to query the client for the indexing status
    }
}
