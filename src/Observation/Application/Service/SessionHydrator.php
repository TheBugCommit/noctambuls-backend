<?php

declare(strict_types=1);

namespace App\Observation\Application\Service;

use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Observation\Domain\Entity\Session;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\ProfileId;

final readonly class SessionHydrator
{
    public function __construct(
        private ProfileRepositoryInterface $profileRepository,
        private CensusRepositoryInterface $censusRepository,
    ) {
    }

    /**
     * @param Session[] $sessions
     * @return array{profileMap: array<string, string>, censusMap: array<string, string>}
     */
    public function hydrate(array $sessions): array
    {
        if (empty($sessions)) {
            return ['profileMap' => [], 'censusMap' => []];
        }

        return [
            'profileMap' => $this->buildProfileMap($sessions),
            'censusMap' => $this->buildCensusMap($sessions),
        ];
    }

    private function buildProfileMap(array $sessions): array
    {
        $annotatorIds = array_unique(array_map(
            fn(Session $s) => new ProfileId($s->annotatorId()->value()),
            $sessions,
        ));

        $profiles = $this->profileRepository->findByIds($annotatorIds);

        $profileMap = [];
        foreach ($profiles as $profile) {
            $profileMap[$profile->id()->value()] = $profile->name()->value();
        }

        return $profileMap;
    }

    private function buildCensusMap(array $sessions): array
    {
        $censusIds = array_unique(array_filter(array_map(
            fn(Session $s) => $s->censusId()?->value(),
            $sessions,
        )));

        if (empty($censusIds)) {
            return [];
        }

        $censuses = $this->censusRepository->findByIds($censusIds);

        $censusMap = [];
        foreach ($censuses as $census) {
            $censusMap[$census->id()->value()] = $census->title()->value();
        }

        return $censusMap;
    }
}
