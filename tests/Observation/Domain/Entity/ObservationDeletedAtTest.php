<?php

declare(strict_types=1);

namespace App\Tests\Observation\Domain\Entity;

use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\Observation\Domain\Entity\Observation;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\ValueObject\Bearing;
use App\Observation\Domain\ValueObject\ContactType;
use App\Observation\Domain\ValueObject\ObservationId;
use App\Observation\Domain\ValueObject\Quantity;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ObservationDeletedAtTest extends TestCase
{
    private function createObservation(): Observation
    {
        $now = new DateTimeImmutable('2026-01-01T00:00:00Z');

        return Observation::create(
            id:               new ObservationId('00000000-0000-0000-0000-000000000001'),
            session:          $this->createMock(Session::class),
            speciesId:        new BirdId('00000000-0000-0000-0000-000000000002'),
            contactType:      ContactType::Visual,
            quantity:         new Quantity(1),
            startTime:        $now,
            endTime:          $now,
            notes:            null,
            latitude:         null,
            longitude:        null,
            accuracy:         null,
            altitude:         null,
            altitudeAccuracy: null,
            locationSource:   null,
            bearing:          new Bearing(0.0),
            createdAt:        $now,
            updatedAt:        $now,
            isDeleted:        false,
        );
    }

    public function test_deleted_at_is_null_on_new_observation(): void
    {
        $obs = $this->createObservation();

        self::assertNull($obs->deletedAt());
    }

    public function test_mark_deleted_sets_deleted_at(): void
    {
        $obs  = $this->createObservation();
        $time = new DateTimeImmutable('2026-05-01T10:00:00Z');

        $obs->markDeleted($time);

        self::assertSame($time, $obs->deletedAt());
    }

    public function test_restore_does_not_clear_deleted_at(): void
    {
        $obs     = $this->createObservation();
        $deleted = new DateTimeImmutable('2026-05-01T10:00:00Z');
        $restored = new DateTimeImmutable('2026-05-02T08:00:00Z');

        $obs->markDeleted($deleted);
        $obs->restore($restored);

        self::assertSame($deleted, $obs->deletedAt());
    }

    public function test_second_delete_updates_deleted_at(): void
    {
        $obs = $this->createObservation();
        $t1  = new DateTimeImmutable('2026-05-01T10:00:00Z');
        $t2  = new DateTimeImmutable('2026-05-02T08:00:00Z');
        $t3  = new DateTimeImmutable('2026-05-03T12:00:00Z');

        $obs->markDeleted($t1);
        $obs->restore($t2);
        $obs->markDeleted($t3);

        self::assertSame($t3, $obs->deletedAt());
    }

    public function test_mark_deleted_sets_is_deleted_true(): void
    {
        $obs = $this->createObservation();

        $obs->markDeleted(new DateTimeImmutable('2026-05-01T10:00:00Z'));

        self::assertTrue($obs->isDeleted());
    }

    public function test_restore_sets_is_deleted_false(): void
    {
        $obs = $this->createObservation();

        $obs->markDeleted(new DateTimeImmutable('2026-05-01T10:00:00Z'));
        $obs->restore(new DateTimeImmutable('2026-05-02T08:00:00Z'));

        self::assertFalse($obs->isDeleted());
    }
}
