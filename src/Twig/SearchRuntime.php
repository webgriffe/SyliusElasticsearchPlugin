<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Twig;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Twig\Extension\RuntimeExtensionInterface;
use Webgriffe\SyliusElasticsearchPlugin\Form\SearchType;

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
