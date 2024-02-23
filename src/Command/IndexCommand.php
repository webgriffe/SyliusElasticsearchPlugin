<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Command;

use InvalidArgumentException;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webgriffe\SyliusElasticsearchPlugin\Message\CreateIndex;
use Webgriffe\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;

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
                $channelId = $channel->getId();
                if (!is_string($channelId) && !is_int($channelId)) {
                    throw new InvalidArgumentException('Channel id must be a string or an integer');
                }
                $this->messageBus->dispatch(new CreateIndex($channelId, $documentType->getCode()));
            }
        }

        return Command::SUCCESS;
    }
}
