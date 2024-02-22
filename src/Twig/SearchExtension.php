<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SearchExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_search_form', [SearchRuntime::class, 'getSearchForm']),
        ];
    }
}
