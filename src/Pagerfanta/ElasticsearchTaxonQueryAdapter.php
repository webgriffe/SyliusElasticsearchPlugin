<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;
use RuntimeException;
use Sylius\Component\Core\Model\TaxonInterface;
use Webgriffe\SyliusElasticsearchPlugin\Builder\QueryBuilderInterface;
use Webgriffe\SyliusElasticsearchPlugin\Client\ClientInterface;
use Webgriffe\SyliusElasticsearchPlugin\Mapper\QueryResultMapperInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\QueryResultInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\ResponseInterface;

/**
 * @psalm-import-type QueryFilters from QueryBuilderInterface
 *
 * @implements AdapterInterface<ResponseInterface>
 */
final class ElasticsearchTaxonQueryAdapter implements AdapterInterface
{
    private ?QueryResultInterface $queryResult = null;

    /** @var int<0,max>|null */
    private ?int $nbResults = null;

    /**
     * @param array<array-key, string> $indexes
     * @param array<string, string> $sorting
     * @param QueryFilters $filters
     */
    public function __construct(
        private readonly QueryBuilderInterface $queryBuilder,
        private readonly ClientInterface $indexManager,
        private readonly QueryResultMapperInterface $queryResultMapper,
        private readonly array $indexes,
        private readonly TaxonInterface $taxon,
        private readonly array $sorting,
        private readonly array $filters,
    ) {
    }

    public function getNbResults(): int
    {
        if ($this->nbResults !== null) {
            return $this->nbResults;
        }
        $this->nbResults = max(0, $this->indexManager->count(
            $this->queryBuilder->buildTaxonQuery($this->taxon),
            $this->indexes,
        ));

        return $this->nbResults;
    }

    public function getSlice(int $offset, int $length): iterable
    {
        $queryResult = $this->doQuery(
            max(0, $offset),
            max(1, min($length, 10_000)),
        );

        return $queryResult->getHints($offset, $length);
    }

    public function getQueryResult(): QueryResultInterface
    {
        $queryResult = $this->queryResult;
        if ($queryResult === null) {
            throw new RuntimeException('Query has not been already executed! Please, remember to call getCurrentPageResults() on paginator before accessing query results.');
        }

        return $queryResult;
    }

    /**
     * @param int<1,10_000> $size
     */
    private function doQuery(
        int $page,
        int $size,
    ): QueryResultInterface {
        if ($this->queryResult !== null) {
            return $this->queryResult;
        }
        $query = $this->queryBuilder->buildTaxonQuery(
            $this->taxon,
            $page,
            $size,
            $this->sorting,
            true,
            $this->filters,
        );
        $esResult = $this->indexManager->query($query, $this->indexes);
        $this->queryResult = $this->queryResultMapper->map($esResult);
        $this->nbResults = max(0, $this->queryResult->getTotalHits());

        return $this->queryResult;
    }
}
