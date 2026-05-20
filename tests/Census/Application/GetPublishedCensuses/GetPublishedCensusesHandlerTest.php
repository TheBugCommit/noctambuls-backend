<?php

declare(strict_types=1);

namespace App\Tests\Census\Application\GetPublishedCensuses;

use App\Census\Application\GetPublishedCensuses\GetPublishedCensusesHandler;
use App\Census\Application\GetPublishedCensuses\GetPublishedCensusesRequest;
use App\Census\Domain\Entity\Census;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusDate;
use App\Census\Domain\ValueObject\CensusId;
use App\Census\Domain\ValueObject\CensusLocationName;
use App\Census\Domain\ValueObject\CensusTime;
use App\Census\Domain\ValueObject\CensusTitle;
use App\Shared\Domain\ValueObject\Latitude;
use App\Shared\Domain\ValueObject\Longitude;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetPublishedCensusesHandlerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private CensusRepositoryInterface&MockObject $repository;
    private GetPublishedCensusesHandler          $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CensusRepositoryInterface::class);
        $this->handler    = new GetPublishedCensusesHandler($this->repository);
    }

    private function createPublishedCensus(string $publishedAgo): Census
    {
        $census = Census::create(
            new CensusId(self::UUID),
            new CensusTitle('Test Census'),
            new CensusDate('2026-06-15'),
            new CensusTime('10:00'),
            new CensusLocationName('Test Location'),
            new Latitude(41.0),
            new Longitude(1.0),
            null,
        );
        $census->publish();

        $reflection = new \ReflectionClass($census);
        $prop       = $reflection->getProperty('publishedAt');
        $prop->setValue($census, new \DateTimeImmutable($publishedAgo, new \DateTimeZone('UTC')));

        return $census;
    }

    public function test_handle_returns_empty_list(): void
    {
        $this->repository->method('findPublishedUpcoming')->willReturn([]);

        $response = $this->handler->handle(new GetPublishedCensusesRequest());

        self::assertSame([], $response->censuses);
        self::assertNotEmpty($response->serverTime);
    }

    public function test_handle_returns_census_outside_grace_period(): void
    {
        $census = $this->createPublishedCensus('-15 minutes');
        $this->repository->method('findPublishedUpcoming')->willReturn([$census]);

        $response = $this->handler->handle(new GetPublishedCensusesRequest());

        self::assertCount(1, $response->censuses);
        self::assertSame(self::UUID, $response->censuses[0]['id']);
    }

    public function test_handle_excludes_census_within_grace_period(): void
    {
        $census = $this->createPublishedCensus('-5 minutes');
        $this->repository->method('findPublishedUpcoming')->willReturn([$census]);

        $response = $this->handler->handle(new GetPublishedCensusesRequest());

        self::assertCount(0, $response->censuses);
    }

    public function test_handle_passes_null_since_by_default(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('findPublishedUpcoming')
            ->with(null)
            ->willReturn([]);

        $this->handler->handle(new GetPublishedCensusesRequest());
    }

    public function test_handle_passes_since_filter(): void
    {
        $since = new \DateTimeImmutable('2026-01-01', new \DateTimeZone('UTC'));

        $this->repository
            ->expects(self::once())
            ->method('findPublishedUpcoming')
            ->with(self::callback(fn(\DateTimeImmutable $s) => $s->getTimestamp() === $since->getTimestamp()))
            ->willReturn([]);

        $this->handler->handle(new GetPublishedCensusesRequest($since));
    }
}
