<?php

declare(strict_types=1);

namespace App\Observation\Infrastructure\Console;

use App\Observation\Application\PurgeExpiredDeletedSessions\PurgeExpiredDeletedSessionsHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:session:purge-expired',
    description: 'Hard-delete soft-deleted sessions older than 30 days.',
)]
final class PurgeExpiredSessionsCommand extends Command
{
    public function __construct(
        private readonly PurgeExpiredDeletedSessionsHandler $handler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $cutoff = new \DateTimeImmutable('-30 days');

        $count = $this->handler->handle($cutoff);

        $io->success(sprintf('Purged %d expired deleted sessions.', $count));

        return Command::SUCCESS;
    }
}
