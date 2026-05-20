<?php

declare(strict_types=1);

namespace App\Tests\Observation\Application\GetSessionDetail;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Observation\Application\GetSessionDetail\GetSessionDetailHandler;
use App\Observation\Application\GetSessionDetail\GetSessionDetailRequest;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Exception\SessionNotFoundException;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\MoonPresence;
use App\Observation\Domain\ValueObject\RainLevel;
use App\Observation\Domain\ValueObject\SessionId;
use App\Observation\Domain\ValueObject\SessionType;
use App\Observation\Domain\ValueObject\SkyCondition;
use App\Observation\Domain\ValueObject\TemperatureRange;
use App\Observation\Domain\ValueObject\VisibilityLevel;
use App\Observation\Domain\ValueObject\WindLevel;
use App\Profile\Domain\Entity\Profile;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\ProfileName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetSessionDetailHandlerTest extends TestCase
{
    private const string SESSION_UUID   = '550e8400-e29b-41d4-a716-446655440000';
    private const string ANNOTATOR_UUID = '123e4567-e89b-12d3-a456-426614174000';

    private SessionRepositoryInterface&MockObject $sessionRepository;
    private ProfileRepositoryInterface&MockObject $profileRepository;
    private CensusRepositoryInterface&MockObject  $censusRepository;
    private BirdRepositoryInterface&MockObject    $birdRepository;
    private GetSessionDetailHandler               $handler;

    protected function setUp(): void
    {
        $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
        $this->profileRepository = $this->createMock(ProfileRepositoryInterface::class);
        $this->censusRepository  = $this->createMock(CensusRepositoryInterface::class);
        $this->birdRepository    = $this->createMock(BirdRepositoryInterface::class);
        $this->handler           = new GetSessionDetailHandler(
            $this->sessionRepository,
            $this->profileRepository,
            $this->censusRepository,
            $this->birdRepository,
        );
    }

    public function test_handle_throws_when_session_not_found(): void
    {
        $this->sessionRepository->method('findByIdsWithRelations')->willReturn([]);

        $this->expectException(SessionNotFoundException::class);
        $this->handler->handle(new GetSessionDetailRequest(self::SESSION_UUID));
    }

    public function test_handle_uses_annotator_id_when_no_profile(): void
    {
        $session = $this->createDummySession();
        $this->sessionRepository->method('findByIdsWithRelations')->willReturn([$session]);
        $this->profileRepository->method('findByIds')->willReturn([]);
        $this->birdRepository->method('findAll')->willReturn([]);

        $response = $this->handler->handle(new GetSessionDetailRequest(self::SESSION_UUID));

        self::assertSame(self::ANNOTATOR_UUID, $response->session['annotatorName']);
    }

    public function test_handle_returns_annotator_name_from_profile(): void
    {
        $session = $this->createDummySession();
        $this->sessionRepository->method('findByIdsWithRelations')->willReturn([$session]);
        $this->birdRepository->method('findAll')->willReturn([]);

        $profileName = $this->createMock(ProfileName::class);
        $profileName->method('value')->willReturn('John Doe');
        $profile = $this->createMock(Profile::class);
        $profile->method('name')->willReturn($profileName);
        $this->profileRepository->method('findByIds')->willReturn([$profile]);

        $response = $this->handler->handle(new GetSessionDetailRequest(self::SESSION_UUID));

        self::assertSame('John Doe', $response->session['annotatorName']);
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
}
