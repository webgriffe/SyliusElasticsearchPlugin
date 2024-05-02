<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\IndexManager;

use Generator;
use Sylius\Component\Core\Model\ChannelInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\Enum\Action;
use Webgriffe\SyliusElasticsearchPlugin\Client\ValueObject\BulkAction;
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
        $bulkActions = [];
        foreach ($documents as $document) {
            $syliusId = null;
            if (array_key_exists('sylius-id', $document)) {
                /** @var string|int $syliusId */
                $syliusId = $document['sylius-id'];
            }
            $bulkActions[] = new BulkAction(Action::CREATE, $indexName, $document, null, $syliusId);
        }

        $countBulkActions = count($bulkActions);
        yield Message::createMessage(sprintf('Indexed 0/%d documents.', $countBulkActions));
        foreach ($this->client->bulk($indexName, $bulkActions) as $documentsIndexed) {
            yield Message::createMessage(sprintf('Indexed %d/%d documents.', $documentsIndexed, $countBulkActions));
        }
        yield Message::createMessage(sprintf('Populated index "%s" with %d documents.', $indexName, $countBulkActions));

        yield Message::createMessage(sprintf('Switching alias "%s" to index "%s".', $aliasName, $indexName));
        $this->client->switchAlias($aliasName, $indexName);

        yield Message::createMessage(sprintf('Removing old indexes responding to wildcard "%s".', $indexesToRemoveWildcard));
        $this->client->removeIndexes($indexesToRemoveWildcard, [$indexName]);
    }

    public function upsertDocument(
        ChannelInterface $channel,
        DocumentTypeInterface $documentType,
        string|int ...$identifiers,
    ): Generator {
        $aliasName = $this->indexNameGenerator->generateAlias($channel, $documentType);

        yield Message::createMessage('Retrieving normalized document to update the index.');
        $bulkActions = [];
        foreach ($identifiers as $identifier) {
            $bulkActions[] = new BulkAction(Action::INDEX, $aliasName, $documentType->getDocument($identifier, $channel), null, $identifier);
        }

        $countBulkActions = count($bulkActions);
        yield Message::createMessage(sprintf('Indexed 0/%d documents.', $countBulkActions));
        foreach ($this->client->bulk($aliasName, $bulkActions) as $documentsIndexed) {
            yield Message::createMessage(sprintf('Indexed %d/%d documents.', $documentsIndexed, $countBulkActions));
        }
        yield Message::createMessage(sprintf('Updated %d documents in alias "%s".', $countBulkActions, $aliasName));
    }
}
