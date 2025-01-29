<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event;

use Pagerfanta\Pagerfanta;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @psalm-import-type ESTermSuggesters from \Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface
 */
final class SearchEvent extends Event
{
    /**
     * @param Pagerfanta<mixed> $paginator
     * @param ESTermSuggesters $termSuggesters
     */
    public function __construct(
        private readonly string $query,
        private readonly Pagerfanta $paginator,
        private readonly array $termSuggesters,
    ) {
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return Pagerfanta<mixed>
     */
    public function getPaginator(): Pagerfanta
    {
        return $this->paginator;
    }

    /**
     * @return ESTermSuggesters
     */
    public function getTermSuggesters(): array
    {
        return $this->termSuggesters;
    }
}
