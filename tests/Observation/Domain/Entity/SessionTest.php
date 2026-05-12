<?php

declare(strict_types=1);

namespace App\Tests\Observation\Domain\Entity;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\Census\Domain\ValueObject\CensusId;
use App\Observation\Domain\Entity\Observation;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Exception\InvalidSessionStateException;
use App\Observation\Domain\ValueObject\Bearing;
use App\Observation\Domain\ValueObject\ContactType;
use App\Observation\Domain\ValueObject\ListeningPoint;
use App\Observation\Domain\ValueObject\LocationSource;
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
use App\Shared\Domain\ValueObject\Latitude;
use App\Shared\Domain\ValueObject\Longitude;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function test_it_creates_incidental_session_successfully_with_minimal_data(): void
    {
        // La incidental només requereix les dades ambientals bàsiques i cap localització
        $session = $this->createValidSession(SessionType::Incidental, null, null, null, null, null, null);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertEquals(SessionType::Incidental, $session->sessionType());
    }

    public function test_it_creates_station_session_successfully(): void
    {
        $session = $this->createValidSession(SessionType::Station);
        $this->assertInstanceOf(Session::class, $session);
    }

    public function test_it_throws_exception_if_census_is_missing_census_id(): void
    {
        $this->expectException(InvalidSessionStateException::class);
        $this->expectExceptionMessage('Census Id required');

        // Intentem crear un Cens sense passar el CensusId
        $this->createValidSession(SessionType::Census, null);
    }

    public function test_it_throws_exception_if_station_is_missing_location(): void
    {
        $this->expectException(InvalidSessionStateException::class);
        $this->expectExceptionMessage('Listening Point required');

        // Intentem crear una estació sense Listening Point
        $this->createValidSession(SessionType::Station, null, null);
    }

    public function test_it_throws_exception_if_station_start_time_is_greater_than_end_time(): void
    {
        $this->expectException(InvalidSessionStateException::class);
        $this->expectExceptionMessage('Session Start Time cannot be greater than End Time');

        $start = new \DateTimeImmutable('2023-10-25 10:00:00');
        $end = new \DateTimeImmutable('2023-10-25 09:00:00'); // Final abans de començar

        $this->createValidSession(SessionType::Station, null, new ListeningPoint(1), new Latitude(41.0), new Longitude(2.0), LocationSource::GPS, $start, $end);
    }

    public function test_it_allows_adding_observation_within_station_time_bounds(): void
    {
        $start = new \DateTimeImmutable('2023-10-25 08:00:00');
        $end = new \DateTimeImmutable('2023-10-25 10:00:00');
        $session = $this->createValidSession(SessionType::Station, null, new ListeningPoint(1), new Latitude(41.0), new Longitude(2.0), LocationSource::GPS, $start, $end);

        // L'observació passa a les 09:00 (Perfecte)
        $obsStart = new \DateTimeImmutable('2023-10-25 09:00:00');
        $observation = $this->createDummyObservation($session, $obsStart, $obsStart);

        $session->putObservation($observation);

        $this->assertCount(1, $session->observations());
    }

    public function test_it_throws_exception_if_observation_is_outside_station_time_bounds(): void
    {
        $this->expectException(InvalidSessionStateException::class);
        $this->expectExceptionMessage('Observation must be strictly within the session time range');

        $start = new \DateTimeImmutable('2023-10-25 08:00:00');
        $end = new \DateTimeImmutable('2023-10-25 10:00:00');
        $session = $this->createValidSession(SessionType::Station, null, new ListeningPoint(1), new Latitude(41.0), new Longitude(2.0), LocationSource::GPS, $start, $end);

        // Observació a les 11:00 (Fora del límit)
        $obsStart = new \DateTimeImmutable('2023-10-25 11:00:00');
        $observation = $this->createDummyObservation($session, $obsStart, $obsStart);

        $session->putObservation($observation);
    }

    public function test_it_allows_adding_incidental_observation_on_the_same_day(): void
    {
        $start = new \DateTimeImmutable('2023-10-25 08:00:00');
        $session = $this->createValidSession(SessionType::Incidental, null, null, null, null, null, $start, null);

        // Observació a la tarda, però el mateix dia (Perfecte)
        $obsStart = new \DateTimeImmutable('2023-10-25 18:00:00');
        $observation = $this->createDummyObservation($session, $obsStart, $obsStart);

        $session->putObservation($observation);
        $this->assertCount(1, $session->observations());
    }

    public function test_it_throws_exception_if_incidental_observation_is_on_a_different_day(): void
    {
        $this->expectException(InvalidSessionStateException::class);
        $this->expectExceptionMessage('Incidental observations must occur on the same day as the session');

        $start = new \DateTimeImmutable('2023-10-25 08:00:00');
        $session = $this->createValidSession(SessionType::Incidental, null, null, null, null, null, $start, null);

        // Observació l'endemà (Invàlid)
        $obsStart = new \DateTimeImmutable('2023-10-26 08:00:00');
        $observation = $this->createDummyObservation($session, $obsStart, $obsStart);

        $session->putObservation($observation);
    }

    private function createValidSession(
        SessionType $type = SessionType::Station,
        ?CensusId $censusId = null,
        // Valors per defecte vàlids per no saturar el test
        ?ListeningPoint $lp = new ListeningPoint(1),
        ?Latitude $lat = new Latitude(41.3851),
        ?Longitude $lon = new Longitude(2.1734),
        ?LocationSource $locSource = LocationSource::GPS,
        ?\DateTimeImmutable $start = new \DateTimeImmutable('2023-01-01 10:00:00'),
        ?\DateTimeImmutable $end = new \DateTimeImmutable('2023-01-01 12:00:00')
    ): Session {
        return Session::create(
            new SessionId('550e8400-e29b-41d4-a716-446655440000'),
            $censusId,
            new AuthUserId('123e4567-e89b-12d3-a456-426614174000'),
            $type,
            $start,
            $end,
            TemperatureRange::TenToTwenty,
            WindLevel::Calm,
            SkyCondition::Clear,
            RainLevel::None,
            VisibilityLevel::Good,
            MoonPresence::Absent,
            $lp, $lat, $lon, null, null, null, $locSource,
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
    }

    private function createDummyObservation(Session $session, \DateTimeImmutable $start, \DateTimeImmutable $end): Observation
    {
        return Observation::create(
            new ObservationId('550e8400-e29b-41d4-a716-446655440005'),
            $session,
            new BirdId('550e8400-e29b-41d4-a716-446655260001'),
            ContactType::Visual,
            new Quantity(1),
            $start,
            $end,
            null, null, null, null, null, null, null,
            new Bearing(0),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
    }
}
