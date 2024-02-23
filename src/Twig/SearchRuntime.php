<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Twig;

use Webgriffe\SyliusElasticsearchPlugin\Form\SearchType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class SearchRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private FormFactoryInterface $formFactory,
    ) {
    }

    public function getSearchForm(): FormView
    {
        return $this->formFactory->create(SearchType::class)->createView();
    }
}
