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

    public function test_put_observation_bumps_session_updated_at(): void
    {
        $start = new \DateTimeImmutable('2023-10-25 08:00:00');
        $end = new \DateTimeImmutable('2023-10-25 10:00:00');
        $sessionUpdatedAt = new \DateTimeImmutable('2023-10-25 08:00:00');
        $session = $this->createSessionWithUpdatedAt(SessionType::Station, $start, $end, $sessionUpdatedAt);

        $obsUpdatedAt = new \DateTimeImmutable('2023-10-25 09:30:00');
        $obsStart = new \DateTimeImmutable('2023-10-25 09:00:00');
        $observation = $this->createDummyObservation($session, $obsStart, $obsStart, $obsUpdatedAt);

        $session->putObservation($observation);

        $this->assertEquals($obsUpdatedAt, $session->updatedAt());
    }

    public function test_put_deleted_observation_skips_bounds_check_and_bumps_session_updated_at(): void
    {
        $start = new \DateTimeImmutable('2023-10-25 08:00:00');
        $end = new \DateTimeImmutable('2023-10-25 10:00:00');
        $sessionUpdatedAt = new \DateTimeImmutable('2023-10-25 08:00:00');
        $session = $this->createSessionWithUpdatedAt(SessionType::Station, $start, $end, $sessionUpdatedAt);

        $obsUpdatedAt = new \DateTimeImmutable('2023-10-25 11:30:00');
        // Observació fora de límits temporals, però is_deleted=true
        $outsideStart = new \DateTimeImmutable('2023-10-25 11:00:00');
        $observation = $this->createDummyObservation($session, $outsideStart, $outsideStart, $obsUpdatedAt, true);

        $session->putObservation($observation);

        $this->assertTrue($observation->isDeleted());
        $this->assertEquals($obsUpdatedAt, $session->updatedAt());
    }

    public function test_mark_session_deleted_sets_flag_and_updates_timestamp(): void
    {
        $start = new \DateTimeImmutable('2023-10-25 08:00:00');
        $session = $this->createSessionWithUpdatedAt(SessionType::Incidental, $start, null, $start);

        $deletedAt = new \DateTimeImmutable('2024-01-15 10:00:00');
        $session->markDeleted($deletedAt);

        $this->assertTrue($session->isDeleted());
        $this->assertEquals($deletedAt, $session->updatedAt());
    }

    public function test_mark_session_deleted_propagates_to_all_its_observations(): void
    {
        // 1. Creem la sessió activa
        $start = new \DateTimeImmutable('2023-10-25 08:00:00');
        $end = new \DateTimeImmutable('2023-10-25 09:00:00');
        $session = $this->createSessionWithUpdatedAt(SessionType::Incidental, $start, $end, $start);

        // 2. Creem les observacions.
        // IMPORTANT: Han de ser del mateix dia que la sessió ('2023-10-25')
        // per no fer saltar el teu "test_it_throws_exception_if_incidental_observation_is_on_a_different_day"
        $obsStart1 = new \DateTimeImmutable('2023-10-25 08:15:00');
        $obsEnd1 = new \DateTimeImmutable('2023-10-25 08:20:00');

        $obsStart2 = new \DateTimeImmutable('2023-10-25 08:30:00');
        $obsEnd2 = new \DateTimeImmutable('2023-10-25 08:35:00');

        $obs1 = $this->createDummyObservation($session, $obsStart1, $obsEnd1, $start, false, '550e8400-e29b-41d4-a716-446655440001');
        $obs2 = $this->createDummyObservation($session, $obsStart2, $obsEnd2, $start, false, '550e8400-e29b-41d4-a716-446655440002');

        $session->putObservation($obs1);
        $session->putObservation($obs2);

        // Verifiquem que inicialment NO estan esborrades
        $this->assertFalse($obs1->isDeleted());
        $this->assertFalse($obs2->isDeleted());

        // 3. Executem l'acció: Esborrem l'Aggregate Root
        $deletedAt = new \DateTimeImmutable('2024-01-15 10:00:00');
        $session->markDeleted($deletedAt);

        // 4. Asserts: Comprovem que el soft-delete s'ha propagat a les filles
        $this->assertTrue($session->isDeleted(), 'La sessió ha d\'estar esborrada');

        $this->assertTrue($obs1->isDeleted(), 'L\'observació 1 ha d\'estar esborrada en cascada');
        $this->assertEquals($deletedAt, $obs1->updatedAt(), 'L\'updateAt de l\'obs 1 ha de coincidir amb l\'esborrat');

        $this->assertTrue($obs2->isDeleted(), 'L\'observació 2 ha d\'estar esborrada en cascada');
        $this->assertEquals($deletedAt, $obs2->updatedAt(), 'L\'updateAt de l\'obs 2 ha de coincidir amb l\'esborrat');
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

    private function createSessionWithUpdatedAt(
        SessionType $type,
        ?\DateTimeImmutable $start,
        ?\DateTimeImmutable $end,
        \DateTimeImmutable $updatedAt,
    ): Session {
        return Session::create(
            new SessionId('550e8400-e29b-41d4-a716-446655440000'),
            null,
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
            $type !== SessionType::Incidental ? new ListeningPoint(1) : null,
            $type !== SessionType::Incidental ? new Latitude(41.0) : null,
            $type !== SessionType::Incidental ? new Longitude(2.0) : null,
            null, null, null,
            $type !== SessionType::Incidental ? LocationSource::GPS : null,
            $updatedAt,
            $updatedAt,
        );
    }

    private function createDummyObservation(
        Session $session,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        ?\DateTimeImmutable $updatedAt = null,
        bool $isDeleted = false,
        string $id = '550e8400-e29b-41d4-a716-446655440005',
    ): Observation {
        return Observation::create(
            new ObservationId($id),
            $session,
            new BirdId('550e8400-e29b-41d4-a716-446655260001'),
            ContactType::Visual,
            new Quantity(1),
            $start,
            $end,
            null, null, null, null, null, null, null,
            new Bearing(0),
            new \DateTimeImmutable(),
            $updatedAt ?? new \DateTimeImmutable(),
            $isDeleted,
        );
    }
}
