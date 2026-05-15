<?php

declare(strict_types=1);

namespace App\Observation\Application\UpdateSession;

use App\Observation\Domain\Exception\SessionNotFoundException;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\ListeningPoint;
use App\Observation\Domain\ValueObject\MoonPresence;
use App\Observation\Domain\ValueObject\RainLevel;
use App\Observation\Domain\ValueObject\SkyCondition;
use App\Observation\Domain\ValueObject\TemperatureRange;
use App\Observation\Domain\ValueObject\VisibilityLevel;
use App\Observation\Domain\ValueObject\WindLevel;
use App\Shared\Domain\Service\TransactionManagerInterface;

final readonly class UpdateSessionHandler
{
    public function __construct(
        private SessionRepositoryInterface  $sessionRepository,
        private TransactionManagerInterface $transactionManager,
    )
    {
    }

    public function handle(UpdateSessionRequest $request): void
    {
        $sessions = $this->sessionRepository->findByIdsWithRelations([$request->sessionId]);
        $session = $sessions[0] ?? null;

        if ($session === null) {
            throw new SessionNotFoundException();
        }

        $this->transactionManager->begin();
        try {
            $session->update(
                censusId: $session->censusId(),
                sessionType: $session->sessionType(),
                startTime: $session->startTime(),
                endTime: $session->endTime(),
                temperature: TemperatureRange::from($request->temperature),
                wind: WindLevel::from($request->wind),
                sky: SkyCondition::from($request->sky),
                rain: RainLevel::from($request->rain),
                visibility: VisibilityLevel::from($request->visibility),
                moon: MoonPresence::from($request->moon),
                listeningPoint: $request->listeningPoint !== null ? new ListeningPoint($request->listeningPoint) : $session->listeningPoint(),
                latitude: $session->latitude(),
                longitude: $session->longitude(),
                accuracy: $session->accuracy(),
                altitude: $session->altitude(),
                altitudeAccuracy: $session->altitudeAccuracy(),
                locationSource: $session->locationSource(),
                updatedAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            );

            $this->sessionRepository->save($session);
            $this->transactionManager->commit();
        } catch (\DomainException|\InvalidArgumentException|\Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }
    }
}
