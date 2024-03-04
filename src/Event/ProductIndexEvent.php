<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event;

use Pagerfanta\Pagerfanta;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Contracts\EventDispatcher\Event;

final  class ProductIndexEvent extends Event
{
    public function __construct(
        private readonly TaxonInterface $taxon,
        private readonly Pagerfanta $paginator,
    ) {
    }

    public function getPaginator(): Pagerfanta
    {
        return $this->paginator;
    }

    public function getTaxon(): TaxonInterface
    {
        return $this->taxon;
    }
}
