<?php

declare(strict_types=1);

namespace App\Tests\Observation\Application\ExportSessions;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\Observation\Application\ExportSessions\ExportSessionsHandler;
use App\Observation\Application\ExportSessions\ExportSessionsRequest;
use App\Observation\Application\Mapper\SessionExportMapper;
use App\Observation\Application\Service\SessionHydrator;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\MoonPresence;
use App\Observation\Domain\ValueObject\RainLevel;
use App\Observation\Domain\ValueObject\SessionId;
use App\Observation\Domain\ValueObject\SessionType;
use App\Observation\Domain\ValueObject\SkyCondition;
use App\Observation\Domain\ValueObject\TemperatureRange;
use App\Observation\Domain\ValueObject\VisibilityLevel;
use App\Observation\Domain\ValueObject\WindLevel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ExportSessionsHandlerTest extends TestCase
{
    private const string SESSION_UUID   = '550e8400-e29b-41d4-a716-446655440000';
    private const string ANNOTATOR_UUID = '123e4567-e89b-12d3-a456-426614174000';

    private SessionRepositoryInterface&MockObject $sessionRepository;
    private SessionHydrator&MockObject            $sessionHydrator;
    private BirdRepositoryInterface&MockObject    $birdRepository;
    private SessionExportMapper&MockObject        $exportMapper;
    private ExportSessionsHandler                 $handler;

    protected function setUp(): void
    {
        $this->sessionRepository = $this->createMock(SessionRepositoryInterface::class);
        $this->sessionHydrator   = $this->createMock(SessionHydrator::class);
        $this->birdRepository    = $this->createMock(BirdRepositoryInterface::class);
        $this->exportMapper      = $this->createMock(SessionExportMapper::class);
        $this->handler           = new ExportSessionsHandler(
            $this->sessionRepository,
            $this->sessionHydrator,
            $this->birdRepository,
            $this->exportMapper,
        );

        $this->exportMapper->method('headers')->willReturn(['col1', 'col2']);
        $this->birdRepository->method('findAll')->willReturn([]);
    }

    public function test_handle_returns_empty_when_no_sessions(): void
    {
        $this->sessionRepository->method('findAllIdsForBackoffice')->willReturn([]);
        $this->exportMapper->method('toFlatRows')->willReturn([]);

        $response = $this->handler->handle(new ExportSessionsRequest(format: 'csv'));

        self::assertSame([], $response->rows);
        self::assertSame('csv', $response->format);
    }

    public function test_handle_processes_sessions_and_returns_rows(): void
    {
        $session = $this->createDummySession();

        $this->sessionRepository->method('findAllIdsForBackoffice')
            ->willReturn([self::SESSION_UUID]);
        $this->sessionRepository->method('findByIdsWithRelations')
            ->with([self::SESSION_UUID])
            ->willReturn([$session]);
        $this->sessionHydrator->method('hydrate')
            ->willReturn(['profileMap' => [], 'censusMap' => []]);
        $this->exportMapper->method('toFlatRows')
            ->willReturn([['col1' => 'val1', 'col2' => 'val2']]);

        $response = $this->handler->handle(new ExportSessionsRequest(format: 'csv'));

        self::assertCount(1, $response->rows);
    }

    public function test_handle_sets_correct_format_in_response(): void
    {
        $this->sessionRepository->method('findAllIdsForBackoffice')->willReturn([]);

        $response = $this->handler->handle(new ExportSessionsRequest(format: 'xlsx'));

        self::assertSame('xlsx', $response->format);
        self::assertStringStartsWith('sessions_export_', $response->filename);
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
