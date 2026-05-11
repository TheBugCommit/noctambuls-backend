<?php

declare(strict_types=1);

namespace App\Observation\Application\SyncUp;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\Observation\Application\SyncUp\Dto\SessionSyncDto;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class SyncUpHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private ProfileRepositoryInterface $profileRepository,
        private TransactionManagerInterface $transactionManager,
        private BirdRepositoryInterface $birdRepository,
        private SessionSynchronizer $synchronizer,
    ) {}

    public function handle(SyncUpRequest $request): SyncUpResponse
    {
        if (empty($request->sessions)) {
            return new SyncUpResponse([], []);
        }

        $synced = [];
        $failed = [];
        $annotatorIdObj = new AuthUserId($request->annotatorId);

        $sessionIds = array_column($request->sessions, 'id');
        $existingSessions = $this->indexById($this->sessionRepository->findByIds($sessionIds));

        $validCodesSet = $this->loadValidCodesSet($request->sessions);

        $validBirdsSet = $this->loadValidBirdsSet($request->sessions);

        $censusIds = $this->extractCensusIds($request->sessions);
        $existingCensusSessions = $this->indexByCensusId(
            $this->sessionRepository->findByCensusIdsAndAnnotator($censusIds, $annotatorIdObj)
        );

        foreach ($request->sessions as $sessionData) {
            $sessionId = $sessionData['id'] ?? null;

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

                $session = $this->synchronizer->sync($dto, $request->annotatorId, $existing, $validCodesSet);

                $this->sessionRepository->save($session);

                $this->transactionManager->commit();
                $transactionStarted = false;
                $synced[] = $sessionId;

                //Free memory
                $this->sessionRepository->free($session);
                unset($existingSessions[$sessionId]);

            } catch (SyncSkippedException) {
                if ($transactionStarted) {
                    $this->transactionManager->commit();
                }
                $synced[] = $sessionId;

                if (isset($existingSessions[$sessionId])) {
                    $this->sessionRepository->free($existingSessions[$sessionId]);
                    unset($existingSessions[$sessionId]);
                }
            } catch (\DomainException | SyncValidationException | \InvalidArgumentException $e) {
                if ($transactionStarted) {
                    $this->transactionManager->rollback();
                }
                $failed[] = ['sessionId' => $sessionId, 'reason' => $e->getMessage()];

                if (isset($existingSessions[$sessionId])) {
                    $this->sessionRepository->free($existingSessions[$sessionId]);
                    unset($existingSessions[$sessionId]);
                }
            } catch (\Throwable $e) {
                if ($transactionStarted) {
                    $this->transactionManager->rollback();
                }
                $failed[] = ['sessionId' => $sessionId, 'reason' => 'internal_error'];
            }
        }

        return new SyncUpResponse($synced, $failed);
    }

    /**
     * Helper per indexar les sessions per ID i trobar-les ràpidament
     */
    private function indexById(array $sessions): array
    {
        $map = [];
        foreach ($sessions as $session) {
            $map[$session->id()->value()] = $session;
        }
        return $map;
    }

    private function loadValidCodesSet(array $sessionsData): array
    {
        $allCodes = [];

        foreach ($sessionsData as $session) {
            foreach ($session['participants'] ?? [] as $participant) {
                if (!empty($participant['ornithologistCode']) && is_string($participant['ornithologistCode'])) {
                    $allCodes[] = $participant['ornithologistCode'];
                }
            }
        }

        if (empty($allCodes)) {
            return $allCodes;
        }

        $uniqueCodes = array_values(array_unique($allCodes));

        $validCodesFromDb = $this->profileRepository->findValidOrnithologistCodes($uniqueCodes);

        return array_fill_keys($validCodesFromDb, true);
    }

    /**
     * Helper per extreure els CensusIds sense valors nuls
     */
    private function extractCensusIds(array $sessions): array
    {
        $censusIds = [];
        foreach ($sessions as $session) {
            if (!empty($session['censusId']) && is_string($session['censusId'])) {
                $censusIds[] = $session['censusId'];
            }
        }
        return array_values(array_unique($censusIds));
    }

    private function loadValidBirdsSet(array $sessionsData): array
    {
        $allBirdIds = [];

        foreach ($sessionsData as $session) {
            foreach ($session['observations'] ?? [] as $observation) {
                if (!empty($observation['speciesId']) && is_string($observation['speciesId'])) {
                    $allBirdIds[] = $observation['speciesId'];
                }
            }
        }

        if (empty($allBirdIds)) {
            return [];
        }

        $uniqueIds = array_values(array_unique($allBirdIds));

        // Cridem al repositori del catàleg d'ocells
        $validIdsFromDb = $this->birdRepository->findValidBirdIds($uniqueIds);

        // Retornem un diccionari ['uuid' => true] per buscar ràpid
        return array_fill_keys($validIdsFromDb, true);
    }

    /**
     * Helper per indexar fàcilment les sessions pel seu Census ID
     */
    private function indexByCensusId(array $sessions): array
    {
        $map = [];
        foreach ($sessions as $session) {
            if ($session->censusId() !== null) {
                $map[$session->censusId()->value()] = $session;
            }
        }
        return $map;
    }
}
