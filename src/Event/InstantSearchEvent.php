<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResultInterface;

/**
 * @psalm-import-type ESTermSuggesters from \Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface
 * @psalm-import-type ESCompletionSuggesters from \Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface
 */
final class InstantSearchEvent extends Event
{
    /**
     * @param ESCompletionSuggesters $completionSuggesters
     */
    public function __construct(
        private readonly string $query,
        private readonly QueryResultInterface $queryResult,
        private readonly array $completionSuggesters,
    ) {
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getQueryResult(): QueryResultInterface
    {
        return $this->queryResult;
    }

    /**
     * @return ESCompletionSuggesters
     */
    public function getCompletionSuggesters(): array
    {
        return $this->completionSuggesters;
    }
}
