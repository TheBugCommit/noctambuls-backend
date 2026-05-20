<?php

declare(strict_types=1);

namespace App\Tests\Observation\Application\Service;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Census\Domain\Entity\Census;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Census\Domain\ValueObject\CensusTitle;
use App\Observation\Application\Service\SessionHydrator;
use App\Observation\Domain\Entity\Session;
use App\Profile\Domain\Entity\Profile;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\ProfileId;
use App\Profile\Domain\ValueObject\ProfileName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SessionHydratorTest extends TestCase
{
    private const string ANNOTATOR_UUID = '123e4567-e89b-12d3-a456-426614174000';
    private const string CENSUS_UUID    = '660e8400-e29b-41d4-a716-446655440000';

    private ProfileRepositoryInterface&MockObject $profileRepository;
    private CensusRepositoryInterface&MockObject  $censusRepository;
    private SessionHydrator                       $hydrator;

    protected function setUp(): void
    {
        $this->profileRepository = $this->createMock(ProfileRepositoryInterface::class);
        $this->censusRepository  = $this->createMock(CensusRepositoryInterface::class);
        $this->hydrator          = new SessionHydrator($this->profileRepository, $this->censusRepository);
    }

    public function test_hydrate_returns_empty_maps_when_sessions_is_empty(): void
    {
        $this->profileRepository->expects(self::never())->method('findByIds');
        $this->censusRepository->expects(self::never())->method('findByIds');

        $result = $this->hydrator->hydrate([]);

        self::assertSame(['profileMap' => [], 'censusMap' => []], $result);
    }

    public function test_hydrate_builds_profile_map_from_annotator_ids(): void
    {
        $session = $this->createMock(Session::class);
        $session->method('annotatorId')->willReturn(new AuthUserId(self::ANNOTATOR_UUID));
        $session->method('censusId')->willReturn(null);

        $profile = $this->createMock(Profile::class);
        $profile->method('id')->willReturn(new ProfileId(self::ANNOTATOR_UUID));
        $profile->method('name')->willReturn(new ProfileName('John Doe'));

        $this->profileRepository->method('findByIds')->willReturn([$profile]);
        $this->censusRepository->expects(self::never())->method('findByIds');

        $result = $this->hydrator->hydrate([$session]);

        self::assertSame('John Doe', $result['profileMap'][self::ANNOTATOR_UUID]);
        self::assertSame([], $result['censusMap']);
    }

    public function test_hydrate_builds_census_map_when_sessions_have_census_id(): void
    {
        $session = $this->createMock(Session::class);
        $session->method('annotatorId')->willReturn(new AuthUserId(self::ANNOTATOR_UUID));
        $session->method('censusId')->willReturn(new CensusId(self::CENSUS_UUID));

        $profile = $this->createMock(Profile::class);
        $profile->method('id')->willReturn(new ProfileId(self::ANNOTATOR_UUID));
        $profile->method('name')->willReturn(new ProfileName('John Doe'));

        $census = $this->createMock(Census::class);
        $census->method('id')->willReturn(new CensusId(self::CENSUS_UUID));
        $census->method('title')->willReturn(new CensusTitle('My Census'));

        $this->profileRepository->method('findByIds')->willReturn([$profile]);
        $this->censusRepository->method('findByIds')->willReturn([$census]);

        $result = $this->hydrator->hydrate([$session]);

        self::assertSame('My Census', $result['censusMap'][self::CENSUS_UUID]);
    }

    public function test_hydrate_skips_census_query_when_no_census_ids(): void
    {
        $session = $this->createMock(Session::class);
        $session->method('annotatorId')->willReturn(new AuthUserId(self::ANNOTATOR_UUID));
        $session->method('censusId')->willReturn(null);

        $profile = $this->createMock(Profile::class);
        $profile->method('id')->willReturn(new ProfileId(self::ANNOTATOR_UUID));
        $profile->method('name')->willReturn(new ProfileName('John Doe'));

        $this->profileRepository->method('findByIds')->willReturn([$profile]);
        $this->censusRepository->expects(self::never())->method('findByIds');

        $result = $this->hydrator->hydrate([$session]);

        self::assertSame([], $result['censusMap']);
    }
}
