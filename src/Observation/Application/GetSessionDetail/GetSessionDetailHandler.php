<?php

declare(strict_types=1);

namespace App\Observation\Application\GetSessionDetail;

use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Observation\Application\Mapper\ObservationResponseMapper;
use App\Observation\Domain\Exception\SessionNotFoundException;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\SessionId;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\ProfileId;

final readonly class GetSessionDetailHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private ProfileRepositoryInterface $profileRepository,
        private CensusRepositoryInterface $censusRepository,
        private BirdRepositoryInterface $birdRepository,
    ) {
    }

    public function handle(GetSessionDetailRequest $request): GetSessionDetailResponse
    {
        $sessions = $this->sessionRepository->findByIdsWithRelations([$request->sessionId]);
        $session = $sessions[0] ?? null;

        if ($session === null) {
            throw new SessionNotFoundException();
        }

        $profile = $this->profileRepository->findByIds([new ProfileId($session->annotatorId()->value())]);
        $annotatorName = !empty($profile) ? $profile[0]->name()->value() : $session->annotatorId()->value();

        $censusTitle = null;
        if ($session->censusId() !== null) {
            $census = $this->censusRepository->findById(new CensusId($session->censusId()->value()));
            $censusTitle = $census?->title()->value();
        }

        $birds = $this->birdRepository->findAll();
        return ObservationResponseMapper::toGetSessionDetailResponse(
            $session,
            $annotatorName,
            $censusTitle,
            $birds
        );
    }
}
