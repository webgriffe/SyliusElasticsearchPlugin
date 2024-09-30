<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ChannelPricing;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Model\ProductOptionTranslation;
use Sylius\Component\Product\Model\ProductOptionValue;
use Sylius\Component\Product\Model\ProductOptionValueTranslation;
use Sylius\Component\Product\Model\ProductVariantTranslation;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Tests\Webgriffe\SyliusElasticsearchPlugin\App\Entity\Product\ProductOption;
use Webgriffe\SyliusElasticsearchPlugin\Serializer\ProductOptionValueNormalizer;
use Webgriffe\SyliusElasticsearchPlugin\Serializer\ProductVariantNormalizer;

class ProductVariantNormalizerTest extends TestCase
{
    private ProductVariantNormalizer $productVariantNormalizer;
    private ProductVariantInterface $productVariantToNormalize;
    private Channel $channel;

    protected function setUp(): void
    {
        $eventDispatcher = new EventDispatcher();
        $this->productVariantNormalizer = new ProductVariantNormalizer(
            $eventDispatcher,
            new Serializer([new ProductOptionValueNormalizer($eventDispatcher)]),
        );

        $this->productVariantToNormalize = new ProductVariant();
        $reflectionProductVariant = new \ReflectionClass(ProductVariant::class);
        $productVariantIdProperty = $reflectionProductVariant->getProperty('id');


        $reflectionProductOption = new \ReflectionClass(ProductOption::class);
        $productOptionIdProperty = $reflectionProductOption->getProperty('id');

        $reflectionProductOptionValue = new \ReflectionClass(ProductOptionValue::class);
        $productOptionValueIdProperty = $reflectionProductOptionValue->getProperty('id');

        $productVariantIdProperty->setValue($this->productVariantToNormalize, 1);
        $this->productVariantToNormalize->setCode('ABARTH_595_HOODIE');
        $this->productVariantToNormalize->setEnabled(true);
        $this->productVariantToNormalize->setPosition(2);
        $this->productVariantToNormalize->setWeight(0.43);
        $this->productVariantToNormalize->setWidth(0.76);
        $this->productVariantToNormalize->setHeight(0.89);
        $this->productVariantToNormalize->setDepth(0.14);
        $this->productVariantToNormalize->setShippingRequired(true);
        $this->productVariantToNormalize->setOnHand(50);
        $this->productVariantToNormalize->setOnHold(47);
        $this->productVariantToNormalize->setTracked(true);
        $this->productVariantToNormalize->setShippingRequired(true);
        $this->productVariantToNormalize->setCreatedAt(new \DateTimeImmutable('2020-01-01 10:00:00', new \DateTimeZone('Europe/Rome')));

        $productVariantEnglishTranslation = new ProductVariantTranslation();
        $productVariantEnglishTranslation->setLocale('en_US');
        $productVariantEnglishTranslation->setName('Abarth 595 Hoodie');
        $this->productVariantToNormalize->addTranslation($productVariantEnglishTranslation);

        $productVariantItalianTranslation = new ProductVariantTranslation();
        $productVariantItalianTranslation->setLocale('it_IT');
        $productVariantItalianTranslation->setName('Felpa Abarth 595');
        $this->productVariantToNormalize->addTranslation($productVariantItalianTranslation);

        $this->channel = new Channel();
        $this->channel->setCode('WEB');

        $channelPricing = new ChannelPricing();
        $channelPricing->setPrice(1000);
        $channelPricing->setOriginalPrice(2000);
        $channelPricing->setMinimumPrice(500);
        $channelPricing->setLowestPriceBeforeDiscount(1500);
        $channelPricing->setProductVariant($this->productVariantToNormalize);
        $channelPricing->setChannelCode($this->channel->getCode());
        $this->productVariantToNormalize->addChannelPricing($channelPricing);

        $sizeProductOption = new ProductOption();
        $productOptionIdProperty->setValue($sizeProductOption, 10);
        $sizeProductOption->setCode('SIZE');
        $sizeProductOption->setFilterable(true);

        $sizeProductOptionTranslation = new ProductOptionTranslation();
        $sizeProductOptionTranslation->setLocale('en_US');
        $sizeProductOptionTranslation->setName('Size');
        $sizeProductOption->addTranslation($sizeProductOptionTranslation);

        $sizeProductOptionTranslation = new ProductOptionTranslation();
        $sizeProductOptionTranslation->setLocale('it_IT');
        $sizeProductOptionTranslation->setName('Taglia');
        $sizeProductOption->addTranslation($sizeProductOptionTranslation);

        $sizeProductOptionValue = new ProductOptionValue();
        $productOptionValueIdProperty->setValue($sizeProductOptionValue, 21);
        $sizeProductOptionValue->setCode('S');
        $sizeProductOptionValue->setOption($sizeProductOption);
        $sizeProductOptionValue->setCurrentLocale('it_IT');
        $sizeProductOptionValue->setCurrentLocale('en_US');

        $sizeProductOptionValueTranslation = new ProductOptionValueTranslation();
        $sizeProductOptionValueTranslation->setLocale('en_US');
        $sizeProductOptionValueTranslation->setValue('Small');
        $sizeProductOptionValue->addTranslation($sizeProductOptionValueTranslation);

        $sizeProductOptionValueTranslation = new ProductOptionValueTranslation();
        $sizeProductOptionValueTranslation->setLocale('it_IT');
        $sizeProductOptionValueTranslation->setValue('Piccola');
        $sizeProductOptionValue->addTranslation($sizeProductOptionValueTranslation);

        $this->productVariantToNormalize->addOptionValue($sizeProductOptionValue);

        $colorProductOption = new ProductOption();
        $productOptionIdProperty->setValue($colorProductOption, 11);
        $colorProductOption->setCode('COLOR');
        $colorProductOption->setFilterable(true);

        $colorProductOptionTranslation = new ProductOptionTranslation();
        $colorProductOptionTranslation->setLocale('en_US');
        $colorProductOptionTranslation->setName('Color');
        $colorProductOption->addTranslation($colorProductOptionTranslation);

        $colorProductOptionTranslation = new ProductOptionTranslation();
        $colorProductOptionTranslation->setLocale('it_IT');
        $colorProductOptionTranslation->setName('Colore');
        $colorProductOption->addTranslation($colorProductOptionTranslation);

        $colorProductOptionValue = new ProductOptionValue();
        $productOptionValueIdProperty->setValue($colorProductOptionValue, 23);
        $colorProductOptionValue->setCode('RED');
        $colorProductOptionValue->setOption($colorProductOption);
        $colorProductOptionValue->setCurrentLocale('it_IT');
        $colorProductOptionValue->setCurrentLocale('en_US');

        $colorProductOptionValueTranslation = new ProductOptionValueTranslation();
        $colorProductOptionValueTranslation->setLocale('en_US');
        $colorProductOptionValueTranslation->setValue('Red');
        $colorProductOptionValue->addTranslation($colorProductOptionValueTranslation);

        $colorProductOptionValueTranslation = new ProductOptionValueTranslation();
        $colorProductOptionValueTranslation->setLocale('it_IT');
        $colorProductOptionValueTranslation->setValue('Rosso');
        $colorProductOptionValue->addTranslation($colorProductOptionValueTranslation);

        $this->productVariantToNormalize->addOptionValue($colorProductOptionValue);
    }

    public function testItIsInstantiable(): void
    {
        $this->assertInstanceOf(ProductVariantNormalizer::class, $this->productVariantNormalizer);
    }

    public function testItIsAnInstanceOfNormalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->productVariantNormalizer);
    }

    public function testItSupportProductVariantInterfaceType(): void
    {
        $supportedTypes= $this->productVariantNormalizer->getSupportedTypes(null);

        $this->assertArrayHasKey(ProductVariantInterface::class, $supportedTypes);
        $this->assertTrue($supportedTypes[ProductVariantInterface::class]);
    }

    public function testItSupportNormalizationWithRightType(): void
    {
        $this->assertTrue($this->productVariantNormalizer->supportsNormalization($this->productVariantToNormalize, null, ['type' => 'webgriffe_sylius_elasticsearch_plugin']));
    }

    public function testItDoesNotSupportNormalizationWithRightType(): void
    {
        $this->assertFalse($this->productVariantNormalizer->supportsNormalization($this->productVariantToNormalize, null, ['type' => 'other']));
    }

    public function testItDoesNotSupportNormalizationWithoutType(): void
    {
        $this->assertFalse($this->productVariantNormalizer->supportsNormalization($this->productVariantToNormalize));
    }

    public function testItNormalizeProductVariant(): void
    {
        $productVariantNormalized = $this->productVariantNormalizer->normalize($this->productVariantToNormalize, null, ['channel' => $this->channel, 'type' => 'webgriffe_sylius_elasticsearch_plugin']);
        $this->assertIsArray($productVariantNormalized);

        $this->assertEquals(1, $productVariantNormalized['sylius-id']);
        $this->assertEquals('ABARTH_595_HOODIE', $productVariantNormalized['code']);
        $this->assertEquals(true, $productVariantNormalized['enabled']);
        $this->assertEquals(2, $productVariantNormalized['position']);
        $this->assertEquals(0.43, $productVariantNormalized['weight']);
        $this->assertEquals(0.76, $productVariantNormalized['width']);
        $this->assertEquals(0.89, $productVariantNormalized['height']);
        $this->assertEquals(0.14, $productVariantNormalized['depth']);
        $this->assertTrue($productVariantNormalized['shipping-required']);
        $this->assertEquals([
            [
                'en_US' => 'Abarth 595 Hoodie',
            ],
            [
                'it_IT' => 'Felpa Abarth 595',
            ],
        ], $productVariantNormalized['name']);
        $this->assertEquals(50, $productVariantNormalized['on-hand']);
        $this->assertEquals(47, $productVariantNormalized['on-hold']);
        $this->assertTrue($productVariantNormalized['is-tracked']);
        $this->assertEquals([
            'price' => 1000,
            'original-price' => 2000,
            'minimum-price' => 500,
            'lowest-price-before-discount' => 1500,
            'applied-promotions' => [],
        ], $productVariantNormalized['price']);
        $this->assertEquals([
            [
                'sylius-id' => 10,
                'code' => 'SIZE',
                'name' => [
                    [
                        'en_US' => 'Size',
                    ],
                    [
                        'it_IT' => 'Taglia',
                    ],
                ],
                'filterable' => true,
                'value' => [
                    'sylius-id' => 21,
                    'code' => 'S',
                    'value' => 'Small',
                    'name' => [
                        [
                            'en_US' => 'Small',
                        ],
                        [
                            'it_IT' => 'Piccola',
                        ],
                    ],
                ],
            ],
            [
                'sylius-id' => 11,
                'code' => 'COLOR',
                'name' => [
                    [
                        'en_US' => 'Color',
                    ],
                    [
                        'it_IT' => 'Colore',
                    ],
                ],
                'filterable' => true,
                'value' => [
                    'sylius-id' => 23,
                    'code' => 'RED',
                    'value' => 'Red',
                    'name' => [
                        [
                            'en_US' => 'Red',
                        ],
                        [
                            'it_IT' => 'Rosso',
                        ],
                    ],
                ],
            ],
        ], $productVariantNormalized['options']);
        $this->assertEquals('2020-01-01T10:00:00+01:00', $productVariantNormalized['created-at']);
    }
}
