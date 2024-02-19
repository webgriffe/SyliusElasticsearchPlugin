<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\ClientBuilder;

use Elasticsearch\ClientBuilder;
use LRuozzi9\SyliusElasticsearchPlugin\Client\ClientInterface;
use LRuozzi9\SyliusElasticsearchPlugin\Client\ElasticsearchClient;
use LRuozzi9\SyliusElasticsearchPlugin\ClientBuilder\Exception\ClientConnectionException;
use Throwable;

final class ElasticsearchClientBuilder implements ClientBuilderInterface
{
    private ?ElasticsearchClient $client = null;

    public function __construct(
        private readonly string $host,
        private readonly string $port,
    ) {
    }

    public function build(): ClientInterface
    {
        if ($this->client !== null) {
            return $this->client;
        }

        try {
            $esClient = ClientBuilder::create()->build();
        } catch (Throwable $e) {
            throw new ClientConnectionException(
                'Could not connect to Elasticsearch server',
                $e->getCode(),
                $e,
            );
        }

        $this->client = new ElasticsearchClient($esClient);

        return $this->client;
    }
}
