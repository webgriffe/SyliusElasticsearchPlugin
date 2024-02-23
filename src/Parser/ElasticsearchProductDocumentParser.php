<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Parser;

use Webgriffe\SyliusElasticsearchPlugin\Factory\ProductResponseFactoryInterface;
use Webgriffe\SyliusElasticsearchPlugin\Model\ProductResponseInterface;
use RuntimeException;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Model\LocaleInterface;

/**
 * @psalm-type LocalizedField = array<array-key, array{locale: string, value: string}>
 */
final class ElasticsearchProductDocumentParser implements DocumentParserInterface
{
    private ?string $defaultLocale = null;

    public function __construct(
        private readonly ProductResponseFactoryInterface $productResponseFactory,
        private readonly LocaleContextInterface $localeContext,
        private readonly ChannelContextInterface $channelContext,
        private readonly string $fallbackLocaleCode,
    ) {
    }

    public function parse(array $document): ProductResponseInterface
    {
        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();
        $defaultLocale = $channel->getDefaultLocale();
        $this->defaultLocale = $this->fallbackLocaleCode;
        if ($defaultLocale instanceof LocaleInterface) {
            $defaultLocaleCode = $defaultLocale->getCode();
            if ($defaultLocaleCode !== null) {
                $this->defaultLocale = $defaultLocaleCode;
            }
        }
        /** @var array{sylius-id: int, code: string, name: LocalizedField, description: LocalizedField, taxons: array, main_taxon: array, slug: LocalizedField} $source */
        $source = $document['_source'];
        $localeCode = $this->localeContext->getLocaleCode();
        $slug = $this->getSlug($source['slug'], $localeCode);
        $productResponse = $this->productResponseFactory->createNew();
        $productResponse->setRouteName('sylius_shop_product_show');
        $productResponse->setRouteParams(['slug' => $slug, '_locale' => $localeCode]);
        $productResponse->setName($this->getValueFromLocalizedField($source['name'], $localeCode));

        return $productResponse;
    }

    /**
     * @param LocalizedField $localizedField
     */
    private function getValueFromLocalizedField(array $localizedField, string $localeCode): ?string
    {
        $fallbackValue = null;
        foreach ($localizedField as $field) {
            if ($field['locale'] === $this->defaultLocale) {
                $fallbackValue = $field['value'];
            }
            if ($field['locale'] === $localeCode) {
                return $field['value'];
            }
        }

        return $fallbackValue;
    }

    /**
     * @param LocalizedField $localizedSlug
     */
    private function getSlug(array $localizedSlug, string $localeCode): string
    {
        foreach ($localizedSlug as $slug) {
            if ($slug['locale'] === $localeCode) {
                return $slug['value'];
            }
        }

        throw new RuntimeException('Slug not found');
    }
}
