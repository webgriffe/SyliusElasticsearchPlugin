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

        yield Message::createMessage(sprintf('Creating mapped index named "%s".', $indexName));
        $this->client->createIndex($indexName, $documentType->getMappings(), $documentType->getSettings());

        // @TODO: speed up this step by introducing a batch size
        yield Message::createMessage('Retrieving normalized documents to populate the index.');
        $documents = $documentType->getDocuments($channel);

        $countDocuments = count($documents);
        yield Message::createMessage(sprintf('Indexed 0/%d documents.', $countDocuments));
        foreach ($this->client->bulk($indexName, $documents) as $documentsIndexed) {
            yield Message::createMessage(sprintf('Indexed %d/%d documents.', $documentsIndexed, $countDocuments));
        }
        yield Message::createMessage(sprintf('Populated index "%s" with %d documents.', $indexName, $countDocuments));

        yield Message::createMessage(sprintf('Switching alias "%s" to index "%s".', $aliasName, $indexName));
        $this->client->switchAlias($aliasName, $indexName);

        yield Message::createMessage(sprintf('Removing old indexes responding to wildcard "%s".', $indexesToRemoveWildcard));
        $this->client->removeIndexes($indexesToRemoveWildcard, [$indexName]);
    }
}
