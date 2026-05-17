<?php

declare(strict_types=1);

namespace App\Tests\Census\Application;

use App\Shared\Domain\ValueObject\Latitude;
use App\Shared\Domain\ValueObject\Longitude;
use PHPUnit\Framework\TestCase;
use App\Census\Application\PublishCensus\PublishCensusHandler;
use App\Census\Application\PublishCensus\PublishCensusRequest;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Census\Domain\Entity\Census;
use App\Census\Domain\ValueObject\CensusId;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class PublishCensusHandlerTest extends TestCase
{
    public function test_handle_publishes_and_saves(): void
    {
        $repo = $this->createMock(CensusRepositoryInterface::class);
        $tx = $this->createMock(TransactionManagerInterface::class);

        $census = Census::create(
            new CensusId('00000000-0000-0000-0000-000000000300'),
            new \App\Census\Domain\ValueObject\CensusTitle('T'),
            new \App\Census\Domain\ValueObject\CensusDate('2023-01-01'),
            new \App\Census\Domain\ValueObject\CensusTime('21:00'),
            new \App\Census\Domain\ValueObject\CensusLocationName('Loc'),
            new Latitude(43.1),
            new Longitude(54.4),
            null,
        );

        $repo->method('findById')->willReturn($census);

        $tx->expects($this->once())->method('begin');
        $tx->expects($this->once())->method('commit');
        $repo->expects($this->once())->method('save');

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $handler = new PublishCensusHandler($repo, $tx, $dispatcher);

        $handler->handle(new PublishCensusRequest('00000000-0000-0000-0000-000000000300'));

        $this->assertSame(\App\Census\Domain\ValueObject\CensusStatus::PUBLISHED, $census->status());
    }

    public function test_handle_throws_when_not_found(): void
    {
        $repo = $this->createMock(CensusRepositoryInterface::class);
        $tx = $this->createMock(TransactionManagerInterface::class);

        $repo->method('findById')->willReturn(null);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $handler = new PublishCensusHandler($repo, $tx, $dispatcher);

        $this->expectException(\App\Census\Domain\Exception\CensusNotFoundException::class);

        $handler->handle(new PublishCensusRequest('00000000-0000-0000-0000-000000000300'));
    }
}
