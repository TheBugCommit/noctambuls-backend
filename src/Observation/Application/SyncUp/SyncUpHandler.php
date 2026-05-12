<?php

declare(strict_types=1);

namespace App\Observation\Application\SyncUp;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusStatus;
use App\Observation\Application\SyncUp\Dto\SessionSyncDto;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class SyncUpHandler
{
    public function __construct(
        private SessionRepositoryInterface  $sessionRepository,
        private ProfileRepositoryInterface  $profileRepository,
        private TransactionManagerInterface $transactionManager,
        private BirdRepositoryInterface     $birdRepository,
        private CensusRepositoryInterface   $censusRepository,
        private SessionSynchronizer         $synchronizer,
    )
    {
    }

    public function handle(SyncUpRequest $request): SyncUpResponse
    {
        if (empty($request->sessions)) {
            return new SyncUpResponse([], []);
        }

        $synced = [];
        $failed = [];
        $annotatorIdObj = new AuthUserId($request->annotatorId);

        [
            'censusIds' => $censusIds,
            'participantCodes' => $participantCodes,
            'birdIds' => $birdIds
        ] = $this->extractPreloadData($request->sessions);

        $sessionIds = array_column($request->sessions, 'id');
        $existingSessions = $this->indexEntities($this->sessionRepository->findByIds($sessionIds), 'id');
        $existingCensusSessions = $this->indexEntities(
            $this->sessionRepository->findByCensusIdsAndAnnotator($censusIds, $annotatorIdObj),
            'censusId'
        );

        $validCodesSet = $this->fetchValidSet($participantCodes, fn($codes) => $this->profileRepository->findValidOrnithologistCodes($codes));
        $validBirdsSet = $this->fetchValidSet($birdIds, fn($ids) => $this->birdRepository->findValidBirdIds($ids));
        $validCensusSet = $this->fetchValidSet($censusIds, fn($ids) => $this->censusRepository->findCensusIdsByStatus($ids, CensusStatus::PUBLISHED));

        foreach ($request->sessions as $sessionData) {
            $sessionId = $sessionData['id'] ?? null;
            $session = null;

            if (!is_string($sessionId)) {
                $failed[] = ['sessionId' => 'unknown', 'reason' => 'invalid_data'];
                continue;
            }

            $transactionStarted = false;
            try {
                $this->transactionManager->begin();
                $transactionStarted = true;

                $dto = SessionSyncDto::fromArray($sessionData);

                if ($dto->censusId !== null) {
                    $existingForCensus = $existingCensusSessions[$dto->censusId] ?? null;

                    if ($existingForCensus !== null && $existingForCensus->id()->value() !== $dto->id) {
                        throw new SyncValidationException('duplicate_session_for_census');
                    }
                }

                $existing = $existingSessions[$sessionId] ?? null;

                $session = $this->synchronizer->sync($dto, $request->annotatorId, $existing, $validCodesSet, $validBirdsSet, $validCensusSet);

                $this->sessionRepository->save($session);

                $this->transactionManager->commit();
                $transactionStarted = false;
                $synced[] = $sessionId;
            } catch (SyncSkippedException) {
                if ($transactionStarted) {
                    $this->transactionManager->commit();
                }
                $synced[] = $sessionId;
            } catch (\DomainException|SyncValidationException|\InvalidArgumentException $e) {
                if ($transactionStarted) {
                    $this->transactionManager->rollback();
                }
                $failed[] = ['sessionId' => $sessionId, 'reason' => $e->getMessage()];
            } catch (\Throwable $e) {
                if ($transactionStarted) {
                    $this->transactionManager->rollback();
                }
                $failed[] = ['sessionId' => $sessionId, 'reason' => 'internal_error'];
            } finally {
                if ($session !== null) {
                    $this->sessionRepository->free($session);
                } elseif (isset($existingSessions[$sessionId])) {
                    $this->sessionRepository->free($existingSessions[$sessionId]);
                }
                unset($existingSessions[$sessionId]);
            }
        }

        return new SyncUpResponse($synced, $failed);
    }

    private function extractPreloadData(array $sessionsData): array
    {
        $censusIds = [];
        $participantCodes = [];
        $birdIds = [];

        foreach ($sessionsData as $session) {
            if (!empty($session['censusId']) && is_string($session['censusId'])) {
                $censusIds[] = $session['censusId'];
            }
            foreach ($session['participants'] ?? [] as $participant) {
                if (!empty($participant['ornithologistCode']) && is_string($participant['ornithologistCode'])) {
                    $participantCodes[] = $participant['ornithologistCode'];
                }
            }
            foreach ($session['observations'] ?? [] as $observation) {
                if (!empty($observation['speciesId']) && is_string($observation['speciesId'])) {
                    $birdIds[] = $observation['speciesId'];
                }
            }
        }

        return [
            'censusIds' => array_values(array_unique($censusIds)),
            'participantCodes' => array_values(array_unique($participantCodes)),
            'birdIds' => array_values(array_unique($birdIds)),
        ];
    }

    private function indexEntities(array $entities, string $voMethodName): array
    {
        $map = [];
        foreach ($entities as $entity) {
            $valueObject = $entity->$voMethodName();
            if ($valueObject !== null) {
                $map[$valueObject->value()] = $entity;
            }
        }
        return $map;
    }

    private function fetchValidSet(array $ids, callable $repositoryMethod): array
    {
        if (empty($ids)) {
            return [];
        }

        $validIds = $repositoryMethod($ids);

        return array_fill_keys($validIds, true);
    }
}
