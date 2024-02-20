<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Command;

use LRuozzi9\SyliusElasticsearchPlugin\Message\CreateIndex;
use LRuozzi9\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class IndexCommand extends Command
{
    public function __construct(
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly DocumentTypeProviderInterface $documentTypeProvider,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ChannelInterface[] $channels */
        $channels = $this->channelRepository->findAll();
        foreach ($channels as $channel) {
            foreach ($this->documentTypeProvider->getDocumentsType() as $documentType) {
                $this->messageBus->dispatch(new CreateIndex($channel->getId(), $documentType->getCode()));
            }
        }

        return Command::SUCCESS;
    }
}
