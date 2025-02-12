<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Resolver;

use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\HttpFoundation\Request;

interface RequestTaxonResolverInterface
{
    public function resolve(Request $request, string $taxonSlug): ?TaxonInterface;
}
