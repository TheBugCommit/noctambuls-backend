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
use PHPUnit\Framework\TestCase;

class ObservationTest extends TestCase
{
    public function test_it_creates_observation_and_updates_data(): void
    {
        $sessionMock = $this->createMock(Session::class);

        $obsId = new ObservationId('550e8400-e29b-41d4-a716-446655440000');
        $birdId = new BirdId('550e8400-e29b-41d4-a716-446655440001');
        $start = new \DateTimeImmutable('2023-01-01 10:30:00');

        $observation = Observation::create(
            $obsId,
            $sessionMock,
            $birdId,
            ContactType::Visual,
            new Quantity(5),
            $start,
            $start,
            'Volant al nord',
            null, null, null, null, null, null,
            new Bearing(90.0),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
        );

        $this->assertEquals($obsId, $observation->id());
        $this->assertEquals(ContactType::Visual, $observation->contactType());
        $this->assertEquals(5, $observation->quantity()->value());
        $this->assertFalse($observation->isDeleted());

        // Testem l'update
        $newBirdId = new BirdId('550e8400-e29b-41d4-a716-446655440001');
        $observation->update(
            $newBirdId,
            ContactType::Auditive,
            new Quantity(10),
            $start,
            $start,
            'Cantant amagat',
            null, null, null, null, null, null,
            new Bearing(180.0),
            new \DateTimeImmutable(),
        );

        $this->assertEquals($newBirdId, $observation->speciesId());
        $this->assertEquals(ContactType::Auditive, $observation->contactType());
        $this->assertEquals(10, $observation->quantity()->value());
        $this->assertFalse($observation->isDeleted());
    }

    public function test_it_marks_observation_as_deleted_and_updates_timestamp(): void
    {
        $sessionMock = $this->createMock(Session::class);
        $start = new \DateTimeImmutable('2023-01-01 10:00:00');
        $createdAt = new \DateTimeImmutable('2023-01-01 10:00:00');

        $observation = Observation::create(
            new ObservationId('550e8400-e29b-41d4-a716-446655440000'),
            $sessionMock,
            new BirdId('550e8400-e29b-41d4-a716-446655440001'),
            ContactType::Visual,
            new Quantity(1),
            $start, $start,
            null, null, null, null, null, null, null,
            new Bearing(0.0),
            $createdAt,
            $createdAt,
        );

        $deletedAt = new \DateTimeImmutable('2023-06-01 08:00:00');
        $observation->markDeleted($deletedAt);

        $this->assertTrue($observation->isDeleted());
        $this->assertEquals($deletedAt, $observation->updatedAt());
    }
}
