<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Factory;

use Webgriffe\SyliusElasticsearchPlugin\Model\ProductResponseInterface;
use Webmozart\Assert\Assert;

final readonly class ProductResponseFactory implements ProductResponseFactoryInterface
{
    /**
     * @param class-string $responseClass
     */
    public function __construct(
        private string $responseClass,
    ) {
    }

    /**
     * @psalm-suppress MixedMethodCall
     */
    public function createNew(): ProductResponseInterface
    {
        $response = new $this->responseClass();
        Assert::isInstanceOf($response, ProductResponseInterface::class);

        return $response;
    }
}
