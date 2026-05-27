<?php

declare(strict_types=1);

namespace App\Analytics\Infrastructure\Console;

use App\Analytics\Application\UpdateAnalytics\UpdateAnalyticsHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:analytics:update',
    description: 'Recalculates analytics data from the last processed timestamp.',
)]
final class UpdateAnalyticsCommand extends Command
{
    public function __construct(
        private readonly UpdateAnalyticsHandler $handler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $result = $this->handler->handle();
            $io->success(sprintf(
                'Analytics updated. Processed at: %s. Data points: %d.',
                $result->processedAt,
                $result->dataPointCount,
            ));
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Failed to update analytics: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
