<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\EventSubscriber;

use Psr\Log\LoggerInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Webgriffe\SyliusElasticsearchPlugin\DocumentType\ProductDocumentType;
use Webgriffe\SyliusElasticsearchPlugin\Message\RemoveDocumentIfExists;
use Webgriffe\SyliusElasticsearchPlugin\Message\UpsertDocument;

final readonly class ProductEventSubscriber implements EventSubscriberInterface
{
    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     */
    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private ChannelRepositoryInterface $channelRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.product.post_create' => 'onProductPostCreateOrUpdate',
            'sylius.product.post_update' => 'onProductPostCreateOrUpdate',
            'sylius.product.pre_delete' => 'onProductPreDelete',
        ];
    }

    public function onProductPostCreateOrUpdate(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }
        $productId = $product->getId();
        if (!is_int($productId) && !is_string($productId)) {
            return;
        }

        try {
            /** @var ChannelInterface[] $allChannels */
            $allChannels = $this->channelRepository->findAll();
            $productChannels = $product->getChannels();
            foreach ($allChannels as $channel) {
                $channelId = $channel->getId();
                if (!is_int($channelId) && !is_string($channelId)) {
                    continue;
                }
                if ($productChannels->contains($channel)) {
                    $this->messageBus->dispatch(new UpsertDocument(
                        $channelId,
                        ProductDocumentType::CODE,
                        $productId,
                    ));

                    continue;
                }
                $this->messageBus->dispatch(new RemoveDocumentIfExists(
                    $channelId,
                    ProductDocumentType::CODE,
                    $productId,
                ));
            }
        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage(), $throwable->getTrace());
        }
    }

    public function onProductPreDelete(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }
        $productId = $product->getId();
        if (!is_int($productId) && !is_string($productId)) {
            return;
        }

        try {
            /** @var ChannelInterface[] $allChannels */
            $allChannels = $this->channelRepository->findAll();
            foreach ($allChannels as $channel) {
                $channelId = $channel->getId();
                if (!is_int($channelId) && !is_string($channelId)) {
                    continue;
                }
                $this->messageBus->dispatch(new RemoveDocumentIfExists(
                    $channelId,
                    ProductDocumentType::CODE,
                    $productId,
                ));
            }
        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage(), $throwable->getTrace());
        }
    }
}
