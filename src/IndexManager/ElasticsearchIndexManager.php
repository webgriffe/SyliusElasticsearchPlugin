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
        yield Message::createMessage(sprintf('Creating mapped index named "%s".', $indexName));

        $documents = $documentType->getDocuments($channel);
        yield Message::createMessage(sprintf('Populating index "%s" with %d documents.', $indexName, count($documents)));

        foreach ($this->client->bulk($indexName, $documents) as $documentsIndexed) {
            yield Message::createMessage(sprintf('Indexed %d/%d documents.', $documentsIndexed, count($documents)));
        }
        yield Message::createMessage(sprintf('Populated index "%s".', $indexName));

        $this->client->switchAlias($aliasName, $indexName);
        yield Message::createMessage(sprintf('Switched alias "%s" to index "%s".', $aliasName, $indexName));

        $this->client->removeIndexes($indexesToRemoveWildcard, [$indexName]);
        yield Message::createMessage(sprintf('Removed old indexes responding to wildcard "%s".', $indexesToRemoveWildcard));
    }
}
