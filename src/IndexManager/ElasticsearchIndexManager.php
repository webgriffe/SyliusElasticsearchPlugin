<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\IndexManager;

use Generator;
use Sylius\Component\Core\Model\ChannelInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\DocumentType\DocumentTypeInterface;
use Webgriffe\SyliusElasticsearchPlugin\Generator\IndexNameGeneratorInterface;
use Webgriffe\SyliusElasticsearchPlugin\IndexManager\Output\Message;

final readonly class ElasticsearchIndexManager implements IndexManagerInterface
{
    public function __construct(
        private ClientInterface $client,
        private IndexNameGeneratorInterface $indexNameGenerator,
    ) {
    }

    public function create(ChannelInterface $channel, DocumentTypeInterface $documentType): Generator
    {
        $indexName = $this->indexNameGenerator->generateName($channel, $documentType);
        $aliasName = $this->indexNameGenerator->generateAlias($channel, $documentType);
        $indexesToRemoveWildcard = $this->indexNameGenerator->generateWildcardPattern($channel, $documentType);

        $this->client->createIndex($indexName, $documentType->getMappings(), $documentType->getSettings());
        yield Message::createMessage(sprintf('Creating index named "%s" having alias "%s".', $indexName, $aliasName));

        $this->client->bulk($indexName, $documentType->getDocuments($channel));

        $this->client->switchAlias($aliasName, $indexName);
        yield Message::createMessage('Switched alias.');

        $this->client->removeIndexes($indexesToRemoveWildcard, [$indexName]);
    }
}
