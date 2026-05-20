<?php

declare(strict_types=1);

namespace App\Tests\Analytics\Application\GetMapBubbleSummary;

use App\Analytics\Application\GetMapBubbleSummary\GetMapBubbleSummaryHandler;
use App\Analytics\Application\GetMapBubbleSummary\GetMapBubbleSummaryRequest;
use App\Analytics\Domain\Repository\MapBubbleSummaryRepositoryInterface;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetMapBubbleSummaryHandlerTest extends TestCase
{
    private MapBubbleSummaryRepositoryInterface&MockObject $repository;
    private GetMapBubbleSummaryHandler                     $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MapBubbleSummaryRepositoryInterface::class);
        $this->handler    = new GetMapBubbleSummaryHandler($this->repository);
    }

    public function test_calls_find_since_with_null_when_no_since_given(): void
    {
        $this->repository->expects(self::once())
            ->method('findSince')
            ->with(null)
            ->willReturn([]);

        $this->handler->handle(new GetMapBubbleSummaryRequest(since: null));
    }

    public function test_parses_since_string_to_utc_datetime(): void
    {
        $sinceString = '2026-05-01T10:00:00Z';

        $this->repository->expects(self::once())
            ->method('findSince')
            ->with(self::callback(function (DateTimeImmutable $dt) use ($sinceString) {
                $expected = new DateTimeImmutable($sinceString, new DateTimeZone('UTC'));
                return $dt->getTimestamp() === $expected->getTimestamp();
            }))
            ->willReturn([]);

        $this->handler->handle(new GetMapBubbleSummaryRequest(since: $sinceString));
    }

    public function test_response_contains_items_from_repository(): void
    {
        $items = [
            ['species_id' => 'abc', 'lat' => '41.39', 'lon' => '2.17', 'year' => '2026', 'count' => '5', 'updated_at' => '2026-05-16T10:00:00Z'],
        ];
        $this->repository->method('findSince')->willReturn($items);

        $response = $this->handler->handle(new GetMapBubbleSummaryRequest(since: null));

        self::assertSame($items, $response->items);
    }

    public function test_response_contains_server_time(): void
    {
        $this->repository->method('findSince')->willReturn([]);

        $response = $this->handler->handle(new GetMapBubbleSummaryRequest(since: null));

        self::assertNotEmpty($response->serverTime);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}/', $response->serverTime);
    }
}
