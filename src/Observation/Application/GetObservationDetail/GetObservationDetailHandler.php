<?php

declare(strict_types=1);

namespace App\Observation\Application\GetObservationDetail;

use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\Observation\Application\Mapper\ObservationResponseMapper;
use App\Observation\Domain\Exception\ObservationNotFoundException;
use App\Observation\Domain\Exception\SessionNotFoundException;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\ObservationId;

final readonly class GetObservationDetailHandler
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private BirdRepositoryInterface $birdRepository,
    ) {
    }

    public function handle(GetObservationDetailRequest $request): GetObservationDetailResponse
    {
        $session = $this->sessionRepository->findByObservationId(new ObservationId($request->observationId));

        if ($session === null) {
            throw new SessionNotFoundException();
        }

        $observation = $session->findObservationById(new ObservationId($request->observationId));

        if ($observation === null) {
            throw new ObservationNotFoundException();
        }

        $birds = $this->birdRepository->findAll();

        return ObservationResponseMapper::toGetObservationDetailResponse($observation, $birds);
    }
}
