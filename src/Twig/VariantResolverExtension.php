<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class VariantResolverExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'sylius_resolve_variant',
                [VariantResolverRuntime::class, 'resolveVariant'],
            ),
        ];
    }
}
