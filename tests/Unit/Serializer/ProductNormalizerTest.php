<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Product\Resolver\DefaultProductVariantResolver;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Webgriffe\SyliusElasticsearchPlugin\Serializer\ProductNormalizer;
use Webgriffe\SyliusElasticsearchPlugin\Serializer\ProductOptionValueNormalizer;
use Webgriffe\SyliusElasticsearchPlugin\Serializer\ProductVariantNormalizer;

class ProductNormalizerTest extends TestCase
{
    private ProductNormalizer $productNormalizer;

    private Product $productToNormalize;
    private Channel $channel;

    protected function setUp(): void
    {
        $eventDispatcher = new EventDispatcher();
        $serializer = new Serializer([new ProductVariantNormalizer($eventDispatcher, new Serializer([new ProductOptionValueNormalizer($eventDispatcher)]))]);
        $this->productNormalizer = new ProductNormalizer(
            new DefaultProductVariantResolver(),
            $eventDispatcher,
            $serializer,
            'en_US',
        );

        $this->productToNormalize = new Product();

        $reflectionProduct = new \ReflectionClass(Product::class);
        $productIdProperty = $reflectionProduct->getProperty('id');
        $productIdProperty->setValue($this->productToNormalize, 1);

        $this->productToNormalize->setCode('ABARTH_595_HOODIE');
        $this->productToNormalize->setEnabled(true);
        $this->productToNormalize->setVariantSelectionMethod(ProductInterface::VARIANT_SELECTION_CHOICE);
        $this->productToNormalize->setCreatedAt(new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('Europe/Rome')));

        $this->channel = new Channel();
    }

    public function testItIsInstantiable(): void
    {
        $this->assertInstanceOf(ProductNormalizer::class, $this->productNormalizer);
    }

    public function testItIsAnInstanceOfNormalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->productNormalizer);
    }

    public function testItSupportProductInterfaceType(): void
    {
        $supportedTypes= $this->productNormalizer->getSupportedTypes(null);

        $this->assertArrayHasKey(ProductInterface::class, $supportedTypes);
        $this->assertTrue($supportedTypes[ProductInterface::class]);
    }

    public function testItSupportNormalizationWithRightType(): void
    {
        $this->assertTrue($this->productNormalizer->supportsNormalization($this->productToNormalize, null, ['type' => 'webgriffe_sylius_elasticsearch_plugin']));
    }

    public function testItDoesNotSupportNormalizationWithRightType(): void
    {
        $this->assertFalse($this->productNormalizer->supportsNormalization($this->productToNormalize, null, ['type' => 'other']));
    }

    public function testItDoesNotSupportNormalizationWithoutType(): void
    {
        $this->assertFalse($this->productNormalizer->supportsNormalization($this->productToNormalize));
    }

    public function testItNormalizeProduct(): void
    {
        $productNormalized = $this->productNormalizer->normalize($this->productToNormalize, null, ['channel' => $this->channel, 'type' => 'webgriffe_sylius_elasticsearch_plugin']);
        $this->assertIsArray($productNormalized);

        $this->assertEquals(1, $productNormalized['sylius-id']);
        $this->assertEquals('ABARTH_595_HOODIE', $productNormalized['code']);
        $this->assertEquals(true, $productNormalized['enabled']);
        $this->assertEquals(ProductInterface::VARIANT_SELECTION_CHOICE, $productNormalized['variant-selection-method']);
        $this->assertEquals(Product::getVariantSelectionMethodLabels()[ProductInterface::VARIANT_SELECTION_CHOICE], $productNormalized['variant-selection-method-label']);
        $this->assertEquals('2020-01-01T10:00:00+01:00', $productNormalized['created-at']);
    }
}
