<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\DocumentType;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class ProductDocumentType implements DocumentTypeInterface
{
    public const CODE = 'product';

    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private NormalizerInterface $normalizer,
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
    public function getDocuments(ChannelInterface $channel): array
    {
        $documents = [];
        /** @var ProductInterface $product */
        foreach ($this->productRepository->findAll() as $product) {
            $documents[] = $this->normalizer->normalize($product, null, [
                'type' => 'webgriffe_sylius_elasticsearch_plugin',
                'channel' => $channel,
            ]);
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
                'slug' => [
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
}
