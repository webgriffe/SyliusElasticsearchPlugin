<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Command;

use InvalidArgumentException;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Webgriffe\SyliusElasticsearchPlugin\IndexManager\IndexManagerInterface;
use Webgriffe\SyliusElasticsearchPlugin\Message\CreateIndex;
use Webgriffe\SyliusElasticsearchPlugin\Provider\DocumentTypeProviderInterface;

final class IndexCommand extends Command
{
    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     */
    public function __construct(
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly DocumentTypeProviderInterface $documentTypeProvider,
        private readonly IndexManagerInterface $indexManager,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates the indexes for all the channels and document types.')
            ->addOption('run-asynchronously', 'async', InputOption::VALUE_OPTIONAL, 'Run the command asynchronously using the message bus.', false)
            ->addArgument(
                'channel-code',
                InputArgument::OPTIONAL,
                'The channel code to create the indexes for. If not provided, all channels will be used.',
                null,
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var bool $runAsynchronously */
        $runAsynchronously = $input->getOption('run-asynchronously');

        $channelCode = $input->getArgument('channel-code');
        if (is_string($channelCode) && $channelCode !== '') {
            $channel = $this->channelRepository->findOneByCode($channelCode);
            if (!$channel instanceof ChannelInterface) {
                $output->writeln(sprintf('<error>Channel with code "%s" not found.</error>', $channelCode));

                return Command::FAILURE;
            }
            $channels = [$channel];
        } else {
            $channels = $this->channelRepository->findAll();
        }
        $documentTypes = $this->documentTypeProvider->getDocumentsType();

        $progressIndicator = new ProgressIndicator($output, null, 5);
        if (!$runAsynchronously) {
            $progressIndicator->start(sprintf('Creating indexes for %d channels and %d document types.', count($channels), count($documentTypes)));
        }
        foreach ($channels as $channel) {
            foreach ($documentTypes as $documentType) {
                $channelId = $channel->getId();
                if (!is_string($channelId) && !is_int($channelId)) {
                    throw new InvalidArgumentException('Channel id must be a string or an integer');
                }
                if ($runAsynchronously) {
                    $this->messageBus->dispatch(new CreateIndex($channelId, $documentType->getCode()));

                    continue;
                }
                foreach ($this->indexManager->create($channel, $documentType) as $message) {
                    $progressIndicator->setMessage((string) $message);
                    $progressIndicator->advance();
                }
            }
        }
        if (!$runAsynchronously) {
            $progressIndicator->finish(sprintf('Finished creating indexes for %d channels and %d document types.', count($channels), count($documentTypes)));
        }

        return Command::SUCCESS;
    }
}
