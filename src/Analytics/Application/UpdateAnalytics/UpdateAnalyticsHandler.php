<?php

declare(strict_types=1);

namespace App\Analytics\Application\UpdateAnalytics;

use App\Analytics\Domain\Entity\CronState;
use App\Analytics\Domain\Event\AnalyticsDataCollectedEvent;
use App\Analytics\Domain\Repository\AnalyticsSessionQueryInterface;
use App\Analytics\Domain\Repository\CronStateRepositoryInterface;
use App\Analytics\Domain\Service\AnalyticsEventDispatcherInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;
use DateTimeImmutable;
use DateTimeZone;

final readonly class UpdateAnalyticsHandler
{
    private const string JOB_NAME = 'update_analytics';

    public function __construct(
        private CronStateRepositoryInterface      $cronStateRepository,
        private AnalyticsSessionQueryInterface    $sessionQuery,
        private AnalyticsEventDispatcherInterface $eventDispatcher,
        private TransactionManagerInterface       $transactionManager,
    ) {}

    public function handle(): UpdateAnalyticsResponse
    {
        $cronState = $this->cronStateRepository->findByJobName(self::JOB_NAME)
            ?? new CronState(self::JOB_NAME, new DateTimeImmutable('@0', new DateTimeZone('UTC')));

        $dataPoints = $this->sessionQuery->findDataPointsSince($cronState->lastProcessedAt());

        $this->transactionManager->begin();
        try {
            $this->eventDispatcher->dispatch(new AnalyticsDataCollectedEvent($dataPoints));

            $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
            $cronState->advance($now);
            $this->cronStateRepository->save($cronState);

            $this->transactionManager->commit();

            return new UpdateAnalyticsResponse(
                processedAt: $now->format('Y-m-d\TH:i:s\Z'),
                dataPointCount: count($dataPoints),
            );
        } catch (\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
