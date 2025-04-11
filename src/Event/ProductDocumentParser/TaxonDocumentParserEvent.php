<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Event\ProductDocumentParser;

use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class TaxonDocumentParserEvent extends Event
{
    /**
     * @param array{sylius-id: int|string, code?: ?string, enabled: bool, left: ?int, right: ?int, level: ?int, position: ?int, root: ?array, parent: ?array, children: array[], name: array<array-key, array<string, string>>, slug: array<array-key, array<string, string>>, description: array<array-key, array<string, string>>} $esTaxon
     */
    public function __construct(
        private readonly array $esTaxon,
        private TaxonInterface $taxon,
    ) {
    }

    /**
     * @return array{sylius-id: int|string, code?: ?string, enabled: bool, left: ?int, right: ?int, level: ?int, position: ?int, root: ?array, parent: ?array, children: array[], name: array<array-key, array<string, string>>, slug: array<array-key, array<string, string>>, description: array<array-key, array<string, string>>}
     */
    public function getEsTaxon(): array
    {
        return $this->esTaxon;
    }

    public function getTaxon(): TaxonInterface
    {
        return $this->taxon;
    }

    public function setTaxon(TaxonInterface $taxon): void
    {
        $this->taxon = $taxon;
    }
}
