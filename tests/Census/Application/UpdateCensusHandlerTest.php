<?php

declare(strict_types=1);

namespace App\Tests\Census\Application;

use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\ValueObject\CensusStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Census\Application\UpdateCensus\UpdateCensusHandler;
use App\Census\Application\UpdateCensus\UpdateCensusRequest;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Census\Domain\Entity\Census;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class UpdateCensusHandlerTest extends TestCase
{
    private CensusRepositoryInterface&MockObject $repository;
    private TransactionManagerInterface&MockObject $transactionManager;
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private UpdateCensusHandler $handler;

    protected function setUp(): void
    {
        $this->repository       = $this->createMock(CensusRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->eventDispatcher  = $this->createMock(EventDispatcherInterface::class);

        $this->handler = new UpdateCensusHandler($this->repository, $this->transactionManager, $this->eventDispatcher);
    }

    public function testItUpdatesACensusSuccessfully(): void
    {
        $request = new UpdateCensusRequest(
            id: '8be4df61-93ca-11d2-aa0d-00e098032b8c',
            title: 'Títol Actualitzat',
            date: '2026-05-12',
            time: '16:00',
            latitude: 42.0,
            longitude: 2.0,
            locationName: 'Nova Localització',
            description: null
        );

        $censusMock = $this->createMock(Census::class);

        $this->repository->method('findById')->willReturn($censusMock);
        $censusMock->method('status')->willReturn(CensusStatus::DRAFT);

        $censusMock->expects($this->once())->method('update');

        $this->transactionManager->expects($this->once())->method('begin');
        $this->repository->expects($this->once())->method('save')->with($censusMock);
        $this->transactionManager->expects($this->once())->method('commit');

        $this->handler->handle($request);
    }

    public function testItThrowsExceptionWhenCensusNotFound(): void
    {
        $request = new UpdateCensusRequest('8be4df61-93ca-11d2-aa0d-00e098032b8c', 'Títol', '2026-05-12', '16:00', 42.0, 2.0, 'Loc', null);

        $this->repository->method('findById')->willReturn(null);

        $this->transactionManager->expects($this->never())->method('begin');

        $this->expectException(CensusNotFoundException::class);

        $this->handler->handle($request);
    }

    public function testItThrowsExceptionOnInvalidDataUpdate(): void
    {
        $request = new UpdateCensusRequest('uuid-123', 'Títol', '2026-05-12', 'invalid-time', 42.0, 2.0, 'Loc', null);

        $censusMock = $this->createMock(Census::class);
        $this->repository->method('findById')->willReturn($censusMock);

        $this->transactionManager->expects($this->never())->method('begin');
        $this->expectException(\InvalidArgumentException::class);

        $this->handler->handle($request);
    }
}
