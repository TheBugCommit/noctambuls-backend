<?php

declare(strict_types=1);

namespace App\Observation\Application\ExportSessions;

use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Observation\Application\Mapper\SessionExportMapper;
use App\Observation\Application\Service\SessionHydrator;
use App\Observation\Domain\Filter\SessionFilter;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\SessionId;
use App\Observation\Domain\ValueObject\SessionType;
use PHPUnit\Framework\Exception;

final readonly class ExportSessionsHandler
{
    private const int BATCH_SIZE = 100;

    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private SessionHydrator $sessionHydrator,
        private BirdRepositoryInterface $birdRepository,
        private SessionExportMapper $exportMapper,
    ) {
    }

    public function handle(ExportSessionsRequest $request): ExportSessionsResponse
    {
        $filter = new SessionFilter(
            sessionId:       $request->sessionId !== null ? new SessionId($request->sessionId) : null,
            sessionType:     $request->sessionType !== null ? SessionType::from($request->sessionType) : null,
            dateFrom:        $request->dateFrom,
            dateTo:          $request->dateTo,
            hasObservations: $request->hasObservations,
            censusId:        $request->censusId !== null ? new CensusId($request->censusId) : null,
            isDeleted:       false,
        );

        $allIds = $this->sessionRepository->findAllIdsForBackoffice($filter);

        $birdMap = [];
        foreach ($this->birdRepository->findAll() as $bird) {
            $birdMap[$bird->id()->value()] = $bird->scientificName()->value();
        }

        $headers = $this->exportMapper->headers();
        $rows = [];

        foreach (array_chunk($allIds, self::BATCH_SIZE) as $batchIds) {
            $sessions = $this->sessionRepository->findByIdsWithRelations($batchIds);

            ['profileMap' => $profileMap, 'censusMap' => $censusMap]
                = $this->sessionHydrator->hydrate($sessions);

            foreach ($this->exportMapper->toFlatRows($sessions, $profileMap, $censusMap, $birdMap) as $row) {
                $rows[] = $row;
            }
        }

        $filename = 'sessions_export_' . new \DateTimeImmutable()->format('Ymd_His');

        return new ExportSessionsResponse(
            headers:  $headers,
            rows:     $rows,
            filename: $filename,
            format:   $request->format,
        );
    }
}
