<?php

declare(strict_types=1);

namespace App\Tests\Analytics\Domain\Entity;

use App\Analytics\Domain\Entity\CronState;
use App\Analytics\Domain\ValueObject\CronJobName;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

final class CronStateTest extends TestCase
{
    public function test_job_name_and_last_processed_at_are_returned(): void
    {
        $time  = new DateTimeImmutable('2026-01-01T00:00:00Z', new DateTimeZone('UTC'));
        $state = new CronState(CronJobName::UpdateAnalytics, $time);

        self::assertSame(CronJobName::UpdateAnalytics, $state->jobName());
        self::assertSame($time, $state->lastProcessedAt());
    }

    public function test_advance_updates_last_processed_at(): void
    {
        $initial = new DateTimeImmutable('2026-01-01T00:00:00Z', new DateTimeZone('UTC'));
        $updated = new DateTimeImmutable('2026-05-16T12:00:00Z', new DateTimeZone('UTC'));
        $state   = new CronState(CronJobName::UpdateAnalytics, $initial);

        $state->advance($updated);

        self::assertSame($updated, $state->lastProcessedAt());
    }

    public function test_advance_does_not_change_job_name(): void
    {
        $state = new CronState(CronJobName::UpdateAnalytics, new DateTimeImmutable('@0'));

        $state->advance(new DateTimeImmutable('2026-05-16T12:00:00Z'));

        self::assertSame(CronJobName::UpdateAnalytics, $state->jobName());
    }
}
