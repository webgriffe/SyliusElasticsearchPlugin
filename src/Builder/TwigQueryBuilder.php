<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Builder;

use const JSON_THROW_ON_ERROR;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Repository\ProductOptionRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Twig\Environment;

final readonly class TwigQueryBuilder implements QueryBuilderInterface
{
    /**
     * @param RepositoryInterface<ProductAttributeInterface> $attributeRepository
     */
    public function __construct(
        private Environment $twig,
        private LocaleContextInterface $localeContext,
        private LoggerInterface $logger,
        private RepositoryInterface $attributeRepository,
        private ProductOptionRepositoryInterface $optionRepository,
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
        $query = $this->twig->render('@WebgriffeSyliusElasticsearchPlugin/query/taxon/query.json.twig', [
            'taxon' => $taxon,
            'filters' => $filters ?? [],
        ]);
        $taxonQuery = [];
        /** @var array $queryNormalized */
        $queryNormalized = json_decode($query, true, 512, JSON_THROW_ON_ERROR);
        $taxonQuery['query'] = $queryNormalized;
        $localeCode = $this->localeContext->getLocaleCode();

        if ($sorting !== null) {
            foreach ($sorting as $field => $order) {
                $sort = $this->twig->render('@WebgriffeSyliusElasticsearchPlugin/query/taxon/sort/' . $field . '.json.twig', [
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
            $aggs = [];
            foreach ($this->attributeRepository->findAll() as $attribute) {
                $aggregation = $this->twig->render('@WebgriffeSyliusElasticsearchPlugin/query/taxon/aggs/attribute.json.twig', [
                    'attribute' => $attribute,
                    'taxon' => $taxon,
                    'localeCode' => $localeCode,
                ]);
                /** @var array $aggregationNormalized */
                $aggregationNormalized = json_decode($aggregation, true, 512, JSON_THROW_ON_ERROR);
                $aggs = array_merge($aggs, $aggregationNormalized);
            }
            foreach ($this->optionRepository->findAll() as $option) {
                $aggregation = $this->twig->render('@WebgriffeSyliusElasticsearchPlugin/query/taxon/aggs/option.json.twig', [
                    'option' => $option,
                    'taxon' => $taxon,
                    'localeCode' => $localeCode,
                ]);
                /** @var array $aggregationNormalized */
                $aggregationNormalized = json_decode($aggregation, true, 512, JSON_THROW_ON_ERROR);
                $aggs = array_merge($aggs, $aggregationNormalized);
            }
            $taxonQuery['aggs'] = $aggs;
        }

        $this->logger->debug(sprintf('Built taxon query: "%s".', json_encode($taxonQuery, JSON_THROW_ON_ERROR)));

        return $taxonQuery;
    }
}
