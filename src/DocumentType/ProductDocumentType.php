<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\DocumentType;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;

final readonly class ProductDocumentType implements DocumentTypeInterface
{
    public const CODE = 'product';

    public function __construct(
        private ProductRepositoryInterface $productRepository,
    ) {
    }

    public function getCode(): string
    {
        return self::CODE;
    }

    /**
     * @TODO Add custom method on repository to get products to index
     * @TODO Use serializer to transform product to array
     */
    public function getDocuments(): array
    {
        $documents = [];
        /** @var ProductInterface $product */
        foreach ($this->productRepository->findAll() as $product) {
            $documents[] = $this->normalizeProduct($product);
        }

        return $documents;
    }

    public function getMappings(): array
    {
        return [
            'properties' => [
                'sylius-id' => [
                    'type' => 'keyword',
                    'index' => false,
                ],
                'code' => [
                    'type' => 'keyword',
                ],
                'name' => [
                    'type' => 'nested',
                    'dynamic' => 'false',
                    'include_in_parent' => true,
                    'properties' => [
                        'locale' => [
                            'type' => 'text',
                            'index' => false,
                        ],
                        'value' => [
                            'type' => 'keyword',
                        ],
                    ],
                ],
                'description' => [
                    'type' => 'nested',
                    'dynamic' => 'false',
                    'include_in_parent' => true,
                    'properties' => [
                        'locale' => [
                            'type' => 'text',
                            'index' => false,
                        ],
                        'value' => [
                            'type' => 'keyword',
                        ],
                    ],
                ],
                'main_taxon' => [
                    'type' => 'object',
                    'dynamic' => false,
                    'enabled' => true,
                    'subobjects' => true,
                    'properties' => [
                        'sylius-id' => [
                            'type' => 'keyword',
                            'index' => false,
                        ],
                        'code' => [
                            'type' => 'keyword',
                            'index' => false,
                        ],
                        'name' => [
                            'type' => 'nested',
                            'dynamic' => 'false',
                            'include_in_parent' => true,
                            'properties' => [
                                'locale' => [
                                    'type' => 'text',
                                    'index' => false,
                                ],
                                'value' => [
                                    'type' => 'keyword',
                                    'index' => false,
                                ],
                            ],
                        ],
                    ],
                ],
                'taxons' => [
                    'type' => 'nested',
                    'dynamic' => 'false',
                    'include_in_parent' => true,
                    'properties' => [
                        'sylius-id' => [
                            'type' => 'keyword',
                            'index' => false,
                        ],
                        'code' => [
                            'type' => 'keyword',
                            'index' => false,
                        ],
                        'name' => [
                            'type' => 'nested',
                            'dynamic' => 'false',
                            'include_in_parent' => true,
                            'properties' => [
                                'locale' => [
                                    'type' => 'text',
                                    'index' => false,
                                ],
                                'value' => [
                                    'type' => 'keyword',
                                    'index' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function normalizeProduct(ProductInterface $product): array
    {
        $normalizedProduct = [
            'sylius-id' => $product->getId(),
            'code' => $product->getCode(),
            'name' => [],
            'description' => [],
            'taxons' => [],
        ];
        /** @var ProductTranslationInterface $productTranslation */
        foreach ($product->getTranslations() as $productTranslation) {
            $normalizedProduct['name'][] = [
                'locale' => $productTranslation->getLocale(),
                'value' => $productTranslation->getName(),
            ];
            $normalizedProduct['description'][] = [
                'locale' => $productTranslation->getLocale(),
                'value' => $productTranslation->getDescription(),
            ];
        }
        $mainTaxon = $product->getMainTaxon();
        if ($mainTaxon instanceof TaxonInterface) {
            $normalizedProduct['main_taxon'] = $this->normalizeTaxon($mainTaxon);
        }
        foreach ($product->getTaxons() as $taxon) {
            $normalizedProduct['taxons'][] = $this->normalizeTaxon($taxon);
        }

        return $normalizedProduct;
    }

    private function normalizeTaxon(TaxonInterface $taxon): array
    {
        $normalizedTaxon = [
            'sylius-id' => $taxon->getId(),
            'code' => $taxon->getCode(),
            'name' => [],
        ];
        /** @var TaxonTranslationInterface $taxonTranslation */
        foreach ($taxon->getTranslations() as $taxonTranslation) {
            $normalizedTaxon['name'][] = [
                'locale' => $taxonTranslation->getLocale(),
                'value' => $taxonTranslation->getName(),
            ];
        }

        return $normalizedTaxon;
    }
}
