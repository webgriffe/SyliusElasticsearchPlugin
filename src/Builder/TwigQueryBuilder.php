<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Builder;

use const JSON_THROW_ON_ERROR;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Twig\Environment;

final readonly class TwigQueryBuilder implements QueryBuilderInterface
{
    public function __construct(
        private Environment $twig,
        private LocaleContextInterface $localeContext,
        private LoggerInterface $logger,
    ) {
    }

    public function buildTaxonQuery(
        TaxonInterface $taxon,
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

        $this->logger->debug(sprintf('Built taxon query: "%s".', json_encode($taxonQuery, JSON_THROW_ON_ERROR)));

        return $taxonQuery;
    }
}
