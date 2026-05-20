<?php

declare(strict_types=1);

namespace App\Tests\Observation\Application\UpdateObservation;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\Observation\Application\UpdateObservation\UpdateObservationHandler;
use App\Observation\Application\UpdateObservation\UpdateObservationRequest;
use App\Observation\Domain\Entity\Observation;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Exception\ObservationNotFoundException;
use App\Observation\Domain\Exception\SessionNotFoundException;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\Bearing;
use App\Observation\Domain\ValueObject\ContactType;
use App\Observation\Domain\ValueObject\MoonPresence;
use App\Observation\Domain\ValueObject\ObservationId;
use App\Observation\Domain\ValueObject\Quantity;
use App\Observation\Domain\ValueObject\RainLevel;
use App\Observation\Domain\ValueObject\SessionId;
use App\Observation\Domain\ValueObject\SessionType;
use App\Observation\Domain\ValueObject\SkyCondition;
use App\Observation\Domain\ValueObject\TemperatureRange;
use App\Observation\Domain\ValueObject\VisibilityLevel;
use App\Observation\Domain\ValueObject\WindLevel;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\ValueObject\Latitude;
use App\Shared\Domain\ValueObject\Longitude;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateObservationHandlerTest extends TestCase
{
    private const string SESSION_UUID   = '550e8400-e29b-41d4-a716-446655440000';
    private const string OBS_UUID       = '550e8400-e29b-41d4-a716-446655440001';
    private const string BIRD_UUID      = '550e8400-e29b-41d4-a716-446655260001';
    private const string ANNOTATOR_UUID = '123e4567-e89b-12d3-a456-426614174000';

    private SessionRepositoryInterface&MockObject  $sessionRepository;
    private TransactionManagerInterface&MockObject $transactionManager;
    private UpdateObservationHandler               $handler;

    protected function setUp(): void
    {
        $this->sessionRepository  = $this->createMock(SessionRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->handler            = new UpdateObservationHandler($this->sessionRepository, $this->transactionManager);
    }

    public function test_handle_throws_when_session_not_found(): void
    {
        $this->sessionRepository->method('findByObservationId')->willReturn(null);

        $this->expectException(SessionNotFoundException::class);
        $this->handler->handle($this->makeRequest());
    }

    public function test_handle_throws_when_observation_not_found(): void
    {
        $session = $this->createDummySession();
        $this->sessionRepository->method('findByObservationId')->willReturn($session);

        $this->expectException(ObservationNotFoundException::class);
        $this->handler->handle($this->makeRequest());
    }

    public function test_handle_uses_existing_location_when_not_provided(): void
    {
        $session     = $this->createDummySession();
        $observation = $this->createDummyObservation($session);
        $session->putObservation($observation);
        $this->sessionRepository->method('findByObservationId')->willReturn($session);

        $this->handler->handle($this->makeRequest(latitude: null, longitude: null));

        self::assertSame(41.0, $observation->latitude()->value());
    }

    public function test_handle_updates_with_new_location_when_provided(): void
    {
        $session     = $this->createDummySession();
        $observation = $this->createDummyObservation($session);
        $session->putObservation($observation);
        $this->sessionRepository->method('findByObservationId')->willReturn($session);

        $this->handler->handle($this->makeRequest(latitude: 50.0, longitude: 3.0));

        self::assertSame(50.0, $observation->latitude()->value());
    }

    private function makeRequest(?float $latitude = null, ?float $longitude = null): UpdateObservationRequest
    {
        return new UpdateObservationRequest(
            observationId: self::OBS_UUID,
            speciesId: self::BIRD_UUID,
            contactType: ContactType::Visual->value,
            quantity: 1,
            notes: null,
            bearing: 0.0,
            latitude: $latitude,
            longitude: $longitude,
        );
    }

    private function createDummySession(): Session
    {
        return Session::create(
            id: new SessionId(self::SESSION_UUID),
            censusId: null,
            annotatorId: new AuthUserId(self::ANNOTATOR_UUID),
            sessionType: SessionType::Incidental,
            startTime: new \DateTimeImmutable('2023-10-25 08:00:00'),
            endTime: new \DateTimeImmutable('2023-10-25 10:00:00'),
            temperature: TemperatureRange::TenToTwenty,
            wind: WindLevel::Calm,
            sky: SkyCondition::Clear,
            rain: RainLevel::None,
            visibility: VisibilityLevel::Good,
            moon: MoonPresence::Absent,
            listeningPoint: null,
            latitude: null,
            longitude: null,
            accuracy: null,
            altitude: null,
            altitudeAccuracy: null,
            locationSource: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    private function createDummyObservation(Session $session): Observation
    {
        $start = new \DateTimeImmutable('2023-10-25 09:00:00');
        return Observation::create(
            new ObservationId(self::OBS_UUID),
            $session,
            new BirdId(self::BIRD_UUID),
            ContactType::Visual,
            new Quantity(1),
            $start,
            $start,
            null,
            new Latitude(41.0),
            new Longitude(2.0),
            null, null, null, null,
            new Bearing(0),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            true,
        );
    }
}
