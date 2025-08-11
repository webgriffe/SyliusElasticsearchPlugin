<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Builder;

use const JSON_THROW_ON_ERROR;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Twig\Environment;
use Webgriffe\SyliusElasticsearchPlugin\Helper\FilterHelper;

final readonly class TwigQueryBuilder implements QueryBuilderInterface
{
    public function __construct(
        private Environment $twig,
        private LocaleContextInterface $localeContext,
        private LoggerInterface $logger,
        private string $searchQueryTemplate,
        private string $taxonQueryTemplate,
    ) {
    }

    public function buildTaxonQuery(
        TaxonInterface $taxon,
        ?int $from = null,
        ?int $size = null,
        ?array $sorting = null,
        bool $withAggregates = false,
        ?array $filters = null,
    ): array {
        $localeCode = $this->localeContext->getLocaleCode();
        $taxonIdsToSearch = array_merge(
            [$taxon->getId()],
            array_map(
                static function (TaxonInterface $taxon): int|string {
                    $taxonId = $taxon->getId();
                    if (!is_int($taxonId) && !is_string($taxonId)) {
                        throw new RuntimeException(sprintf('Taxon ID must be an integer or a string, got "%s".', gettype($taxonId)));
                    }

                    return $taxonId;
                },
                $this->getTaxonChildren($taxon),
            ),
        );
        $query = $this->twig->render($this->taxonQueryTemplate, [
            'taxon' => $taxon,
            'filters' => $filters ?? FilterHelper::retrieveFilters(),
            'localeCode' => $localeCode,
            'taxonIdsToSearch' => $taxonIdsToSearch,
        ]);
        $taxonQuery = [];
        /** @var array $queryNormalized */
        $queryNormalized = json_decode($query, true, 512, JSON_THROW_ON_ERROR);
        $taxonQuery['query'] = $queryNormalized;

        if ($sorting !== null) {
            foreach ($sorting as $field => $order) {
                $sortFileName = '@WebgriffeSyliusElasticsearchPlugin/query/taxon/sort/' . $field . '.json.twig';
                if (!$this->twig->getLoader()->exists($sortFileName)) {
                    $sortFileName = '@WebgriffeSyliusElasticsearchPlugin/query/common/sort/default.json.twig';
                }
                $sort = $this->twig->render($sortFileName, [
                    'field' => $field,
                    'order' => $order,
                    'taxon' => $taxon,
                    'localeCode' => $localeCode,
                ]);
                /** @var array $sortNormalized */
                $sortNormalized = json_decode($sort, true, 512, JSON_THROW_ON_ERROR);
                $taxonQuery['sort'][] = $sortNormalized;
            }
        }
        if ($from !== null) {
            $taxonQuery['from'] = $from;
        }
        if ($size !== null) {
            $taxonQuery['size'] = $size;
        }

        if ($withAggregates) {
            $attributeAggregationRaw = $this->twig->render('@WebgriffeSyliusElasticsearchPlugin/query/taxon/aggs/attributes.json.twig', [
                'taxon' => $taxon,
                'localeCode' => $localeCode,
            ]);
            /** @var array $attributeAggregationNormalized */
            $attributeAggregationNormalized = json_decode($attributeAggregationRaw, true, 512, JSON_THROW_ON_ERROR);

            $optionAggregationRaw = $this->twig->render('@WebgriffeSyliusElasticsearchPlugin/query/taxon/aggs/options.json.twig', [
                'taxon' => $taxon,
                'localeCode' => $localeCode,
            ]);
            /** @var array $optionAggregationNormalized */
            $optionAggregationNormalized = json_decode($optionAggregationRaw, true, 512, JSON_THROW_ON_ERROR);

            $taxonQuery['aggs'] = array_merge(
                $attributeAggregationNormalized,
                $optionAggregationNormalized,
            );
        }

        $this->logger->debug(sprintf('Built taxon query: "%s".', json_encode($taxonQuery, JSON_THROW_ON_ERROR)));

        return $taxonQuery;
    }

    public function buildSearchQuery(
        string $searchTerm,
        ?int $from = null,
        ?int $size = null,
        ?array $sorting = null,
        bool $withAggregates = false,
        ?array $filters = null,
        ?float $minScore = null,
    ): array {
        $localeCode = $this->localeContext->getLocaleCode();
        $query = $this->twig->render($this->searchQueryTemplate, [
            'searchTerm' => $searchTerm,
            'filters' => $filters ?? FilterHelper::retrieveFilters(),
            'localeCode' => $localeCode,
        ]);
        $searchQuery = [];
        /** @var array $queryNormalized */
        $queryNormalized = json_decode($query, true, 512, JSON_THROW_ON_ERROR);
        $searchQuery['query'] = $queryNormalized;
        if ($minScore !== null) {
            $searchQuery['min_score'] = $minScore;
        }

        if ($sorting !== null) {
            foreach ($sorting as $field => $order) {
                $sortFileName = '@WebgriffeSyliusElasticsearchPlugin/query/search/sort/' . $field . '.json.twig';
                if (!$this->twig->getLoader()->exists($sortFileName)) {
                    $sortFileName = '@WebgriffeSyliusElasticsearchPlugin/query/common/sort/default.json.twig';
                }
                $sort = $this->twig->render($sortFileName, [
                    'field' => $field,
                    'order' => $order,
                    'searchTerm' => $searchTerm,
                    'localeCode' => $localeCode,
                ]);
                /** @var array $sortNormalized */
                $sortNormalized = json_decode($sort, true, 512, JSON_THROW_ON_ERROR);
                $searchQuery['sort'][] = $sortNormalized;
            }
        }
        if ($from !== null) {
            $searchQuery['from'] = $from;
        }
        if ($size !== null) {
            $searchQuery['size'] = $size;
        }

        if ($withAggregates) {
            $attributeAggregationRaw = $this->twig->render('@WebgriffeSyliusElasticsearchPlugin/query/search/aggs/attributes.json.twig', [
                'searchTerm' => $searchTerm,
                'localeCode' => $localeCode,
            ]);
            /** @var array $attributeAggregationNormalized */
            $attributeAggregationNormalized = json_decode($attributeAggregationRaw, true, 512, JSON_THROW_ON_ERROR);

            $optionAggregationRaw = $this->twig->render('@WebgriffeSyliusElasticsearchPlugin/query/search/aggs/options.json.twig', [
                'searchTerm' => $searchTerm,
                'localeCode' => $localeCode,
            ]);
            /** @var array $optionAggregationNormalized */
            $optionAggregationNormalized = json_decode($optionAggregationRaw, true, 512, JSON_THROW_ON_ERROR);

            $searchQuery['aggs'] = array_merge(
                $attributeAggregationNormalized,
                $optionAggregationNormalized,
            );
        }

        $this->logger->debug(sprintf('Built search query: "%s".', json_encode($searchQuery, JSON_THROW_ON_ERROR)));

        return $searchQuery;
    }

    public function buildCompletionSuggestersQuery(
        string $searchTerm,
        ?string $source = 'suggest',
        int $size = 5,
    ): array {
        $localeCode = $this->localeContext->getLocaleCode();
        $query = $this->twig->render('@WebgriffeSyliusElasticsearchPlugin/query/completion-suggesters/query.json.twig', [
            'searchTerm' => $searchTerm,
            'localeCode' => $localeCode,
            'size' => $size,
        ]);
        $completionSuggestersQuery = [];
        /** @var array $queryNormalized */
        $queryNormalized = json_decode($query, true, 512, JSON_THROW_ON_ERROR);
        $completionSuggestersQuery['suggest'] = $queryNormalized;
        if ($source !== null) {
            $completionSuggestersQuery['_source'] = $source;
        }

        $this->logger->debug(sprintf(
            'Built completion suggesters query: "%s".',
            json_encode($completionSuggestersQuery, JSON_THROW_ON_ERROR),
        ));

        return $completionSuggestersQuery;
    }

    public function buildTermSuggestersQuery(
        string $searchTerm,
    ): array {
        $localeCode = $this->localeContext->getLocaleCode();
        $query = $this->twig->render('@WebgriffeSyliusElasticsearchPlugin/query/term-suggesters/query.json.twig', [
            'searchTerm' => $searchTerm,
            'localeCode' => $localeCode,
        ]);
        $termSuggestersQuery = [];
        /** @var array $queryNormalized */
        $queryNormalized = json_decode($query, true, 512, JSON_THROW_ON_ERROR);
        $termSuggestersQuery['suggest'] = $queryNormalized;

        $this->logger->debug(sprintf(
            'Built term suggesters query: "%s".',
            json_encode($termSuggestersQuery, JSON_THROW_ON_ERROR),
        ));

        return $termSuggestersQuery;
    }

    /**
     * @return TaxonInterface[]
     */
    private function getTaxonChildren(TaxonInterface $taxon): array
    {
        $children = [];
        foreach ($taxon->getChildren() as $child) {
            if (!$child instanceof TaxonInterface) {
                continue;
            }
            $children = array_merge($children, $this->getTaxonChildren($child));
        }

        return array_merge($children, [$taxon]);
    }
}
