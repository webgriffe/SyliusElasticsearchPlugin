<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Applicator;

use Sylius\Bundle\CoreBundle\CatalogPromotion\Applicator\CatalogPromotionApplicatorInterface;
use Sylius\Component\Core\Model\CatalogPromotionInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Throwable;
use Webgriffe\SyliusElasticsearchPlugin\DocumentType\ProductDocumentType;
use Webgriffe\SyliusElasticsearchPlugin\IndexManager\IndexManagerInterface;
use Webgriffe\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;

final readonly class CatalogPromotionApplicator implements CatalogPromotionApplicatorInterface
{
    public function __construct(
        private CatalogPromotionApplicatorInterface $decoratedCatalogPromotionApplicator,
        private IndexManagerInterface $indexManager,
        private DocumentTypeProviderInterface $documentTypeProvider,
    ) {
    }

    /**
     * @psalm-suppress UnusedForeachValue
     */
    public function applyOnVariant(ProductVariantInterface $variant, CatalogPromotionInterface $catalogPromotion): void
    {
        $this->decoratedCatalogPromotionApplicator->applyOnVariant($variant, $catalogPromotion);
        $product = $variant->getProduct();
        if (!$product instanceof ProductInterface) {
            return;
        }
        $documentType = $this->documentTypeProvider->getDocumentType(ProductDocumentType::CODE);

        try {
            foreach ($catalogPromotion->getChannels() as $channel) {
                if (!$channel instanceof ChannelInterface) {
                    continue;
                }
                $productId = $product->getId();
                if (!is_string($productId) && !is_int($productId)) {
                    continue;
                }
                foreach ($this->indexManager->upsertDocuments(
                    $channel,
                    $documentType,
                    $productId,
                ) as $outputMessage) {
                }
            }
        } catch (Throwable) {
        }
    }
}
