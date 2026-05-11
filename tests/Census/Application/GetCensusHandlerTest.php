<?php

declare(strict_types=1);

namespace App\Tests\Census\Application;

use App\Census\Application\GetCensus\GetCensusResponse;
use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\ValueObject\CensusDate;
use App\Census\Domain\ValueObject\CensusDescription;
use App\Census\Domain\ValueObject\CensusLocationName;
use App\Census\Domain\ValueObject\CensusTime;
use App\Census\Domain\ValueObject\CensusTitle;
use App\Shared\Domain\ValueObject\Latitude;
use App\Shared\Domain\ValueObject\Longitude;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Census\Application\GetCensus\GetCensusHandler;
use App\Census\Application\GetCensus\GetCensusRequest;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\Entity\Census;
use App\Census\Domain\ValueObject\CensusId;

final class GetCensusHandlerTest extends TestCase
{
    private CensusRepositoryInterface&MockObject $repository;
    private GetCensusHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CensusRepositoryInterface::class);
        $this->handler = new GetCensusHandler($this->repository);
    }

    public function testItGetsACensusSuccessfully(): void
    {
        $request = new GetCensusRequest('8be4df61-93ca-11d2-aa0d-00e098032b8c');

        // Per testar el mapeig, crearem una entitat real
        $census = Census::create(
            new CensusId('8be4df61-93ca-11d2-aa0d-00e098032b8c'),
            new CensusTitle('Títol'),
            new CensusDate('2026-05-11'),
            new CensusTime('10:00'),
            new CensusLocationName('Lloc'),
            new Latitude(41.0),
            new Longitude(1.0),
            new CensusDescription('Desc')
        );

        $this->repository->method('findById')->willReturn($census);

        $response = $this->handler->handle($request);

        $this->assertInstanceOf(GetCensusResponse::class, $response);
        $this->assertEquals('8be4df61-93ca-11d2-aa0d-00e098032b8c', $response->id);
        $this->assertEquals('Títol', $response->title);
    }

    public function testItThrowsExceptionWhenCensusNotFound(): void
    {
        $request = new GetCensusRequest('8be4df61-93ca-11d2-aa0d-00e098032b8c');

        $this->repository->method('findById')->willReturn(null);

        $this->expectException(CensusNotFoundException::class);

        $this->handler->handle($request);
    }
}
