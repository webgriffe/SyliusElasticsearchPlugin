<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Builder;

use const JSON_THROW_ON_ERROR;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
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
    ) {
    }

    public function buildTaxonQuery(
        TaxonInterface $taxon,
        int $from = 0,
        int $size = 10,
        array $sorting = [],
    ): array {
        $query = $this->twig->render('@WebgriffeSyliusElasticsearchPlugin/query/taxon/query.json.twig', [
            'taxon' => $taxon,
        ]);
        $taxonQuery = [];
        /** @var array $queryNormalized */
        $queryNormalized = json_decode($query, true, 512, JSON_THROW_ON_ERROR);
        $taxonQuery['query'] = $queryNormalized;
        $localeCode = $this->localeContext->getLocaleCode();

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
        $taxonQuery['from'] = $from;
        $taxonQuery['size'] = $size;

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
        $taxonQuery['aggs'] = $aggs;

        $this->logger->debug(sprintf('Built taxon query: "%s".', json_encode($taxonQuery, JSON_THROW_ON_ERROR)));

        return $taxonQuery;
    }
}
