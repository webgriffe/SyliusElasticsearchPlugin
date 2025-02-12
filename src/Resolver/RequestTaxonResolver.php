<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Resolver;

use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final readonly class RequestTaxonResolver implements RequestTaxonResolverInterface
{
    /**
     * @param TaxonRepositoryInterface<TaxonInterface> $taxonRepository
     */
    public function __construct(
        private TaxonRepositoryInterface $taxonRepository,
        private LocaleContextInterface $localeContext,
    ) {
    }

    public function resolve(Request $request, string $taxonSlug): ?TaxonInterface
    {
        $localeCode = $this->localeContext->getLocaleCode();

        $taxon = $this->taxonRepository->findOneBySlug($taxonSlug, $localeCode);
        Assert::nullOrIsInstanceOf($taxon, TaxonInterface::class);

        return $taxon;
    }
}
