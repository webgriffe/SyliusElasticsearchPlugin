<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\ClientBuilder;

use LRuozzi9\SyliusElasticsearchPlugin\Client\ClientInterface;
use LRuozzi9\SyliusElasticsearchPlugin\ClientBuilder\Exception\ClientConnectionException;

interface ClientBuilderInterface
{
    /**
     * @throws ClientConnectionException
     */
    public function build(): ClientInterface;
}
