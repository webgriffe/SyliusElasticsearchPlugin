<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Product\Model\ProductOptionValue;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Product\Model\ProductOptionValueTranslation;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tests\Webgriffe\SyliusElasticsearchPlugin\App\Entity\Product\ProductOption;
use Webgriffe\SyliusElasticsearchPlugin\Serializer\ProductOptionValueNormalizer;

class ProductOptionValueNormalizerTest extends TestCase
{
    private ProductOptionValueNormalizer $productOptionValueNormalizer;
    private ProductOptionValueInterface $productOptionValueToNormalize;
    private Channel $channel;

    protected function setUp(): void
    {
        $this->productOptionValueNormalizer = new ProductOptionValueNormalizer(
            new EventDispatcher(),
        );

        $this->productOptionValueToNormalize = new ProductOptionValue();

        $reflectionProductOptionValue = new \ReflectionClass(ProductOptionValue::class);
        $productOptionValueIdProperty = $reflectionProductOptionValue->getProperty('id');

        $sizeProductOption = new ProductOption();

        $productOptionValueIdProperty->setValue($this->productOptionValueToNormalize, 21);
        $this->productOptionValueToNormalize->setCode('S');
        $this->productOptionValueToNormalize->setOption($sizeProductOption);
        $this->productOptionValueToNormalize->setCurrentLocale('it_IT');
        $this->productOptionValueToNormalize->setFallbackLocale('en_US');

        $sizeProductOptionValueTranslation = new ProductOptionValueTranslation();
        $sizeProductOptionValueTranslation->setLocale('en_US');
        $sizeProductOptionValueTranslation->setValue('Small');
        $this->productOptionValueToNormalize->addTranslation($sizeProductOptionValueTranslation);

        $sizeProductOptionValueTranslation = new ProductOptionValueTranslation();
        $sizeProductOptionValueTranslation->setLocale('it_IT');
        $sizeProductOptionValueTranslation->setValue('Piccola');
        $this->productOptionValueToNormalize->addTranslation($sizeProductOptionValueTranslation);

        $this->channel = new Channel();
    }

    public function testItIsInstantiable(): void
    {
        $this->assertInstanceOf(ProductOptionValueNormalizer::class, $this->productOptionValueNormalizer);
    }

    public function testItIsAnInstanceOfNormalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->productOptionValueNormalizer);
    }

    public function testItSupportProductVariantInterfaceType(): void
    {
        $supportedTypes= $this->productOptionValueNormalizer->getSupportedTypes(null);

        $this->assertArrayHasKey(ProductOptionValueInterface::class, $supportedTypes);
        $this->assertTrue($supportedTypes[ProductOptionValueInterface::class]);
    }

    public function testItSupportNormalizationWithRightType(): void
    {
        $this->assertTrue($this->productOptionValueNormalizer->supportsNormalization($this->productOptionValueToNormalize, null, ['type' => 'webgriffe_sylius_elasticsearch_plugin']));
    }

    public function testItDoesNotSupportNormalizationWithRightType(): void
    {
        $this->assertFalse($this->productOptionValueNormalizer->supportsNormalization($this->productOptionValueToNormalize, null, ['type' => 'other']));
    }

    public function testItDoesNotSupportNormalizationWithoutType(): void
    {
        $this->assertFalse($this->productOptionValueNormalizer->supportsNormalization($this->productOptionValueToNormalize));
    }

    public function testItNormalizeProductVariant(): void
    {
        $productOptionValueNormalized = $this->productOptionValueNormalizer->normalize($this->productOptionValueToNormalize, null, ['channel' => $this->channel]);
        $this->assertIsArray($productOptionValueNormalized);

        $this->assertEquals(21, $productOptionValueNormalized['sylius-id']);
        $this->assertEquals('S', $productOptionValueNormalized['code']);
        $this->assertEquals('Piccola', $productOptionValueNormalized['value']);
        $this->assertEquals([
            ['en_US' => 'Small'],
            ['it_IT' => 'Piccola'],
        ], $productOptionValueNormalized['name']);
    }
}
