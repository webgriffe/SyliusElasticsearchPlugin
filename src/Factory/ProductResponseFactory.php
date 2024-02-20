<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Factory;

use LRuozzi9\SyliusElasticsearchPlugin\Model\ProductResponseInterface;
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
