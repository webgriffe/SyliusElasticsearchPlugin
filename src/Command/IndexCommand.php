<?php

declare(strict_types=1);

namespace LRuozzi9\SyliusElasticsearchPlugin\Command;

use LRuozzi9\SyliusElasticsearchPlugin\Message\CreateIndex;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class IndexCommand extends Command
{
    public function __construct(
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly MessageBusInterface $messageBus,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $channels = $this->channelRepository->findAll();
        foreach ($channels as $channel) {
            $this->messageBus->dispatch(new CreateIndex($channel->getId()));
        }

        return Command::SUCCESS;
    }
}
