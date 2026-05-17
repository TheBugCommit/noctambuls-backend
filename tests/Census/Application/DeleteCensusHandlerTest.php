<?php

declare(strict_types=1);

namespace App\Tests\Census\Application;

use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\ValueObject\CensusStatus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Census\Application\DeleteCensus\DeleteCensusHandler;
use App\Census\Application\DeleteCensus\DeleteCensusRequest;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Census\Domain\Entity\Census;
use App\Census\Domain\ValueObject\CensusId;

final class DeleteCensusHandlerTest extends TestCase
{
    private CensusRepositoryInterface&MockObject $repository;
    private TransactionManagerInterface&MockObject $transactionManager;
    private DeleteCensusHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CensusRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->handler = new DeleteCensusHandler($this->repository, $this->transactionManager);
    }

    public function testItHardDeletesDraftCensus(): void
    {
        $request = new DeleteCensusRequest('8be4df61-93ca-11d2-aa0d-00e098032b8c');
        $censusMock = $this->createMock(Census::class);

        $this->repository->method('findById')->willReturn($censusMock);
        $censusMock->method('status')->willReturn(CensusStatus::DRAFT);

        $this->transactionManager->expects($this->once())->method('begin');
        $this->repository->expects($this->once())->method('delete')->with($censusMock);
        $this->repository->expects($this->never())->method('save');
        $this->transactionManager->expects($this->once())->method('commit');

        $this->handler->handle($request);
    }

    public function testItHardDeletesPublishedCensusWithinGracePeriod(): void
    {
        $request = new DeleteCensusRequest('8be4df61-93ca-11d2-aa0d-00e098032b8c');
        $censusMock = $this->createMock(Census::class);

        $this->repository->method('findById')->willReturn($censusMock);
        $censusMock->method('status')->willReturn(CensusStatus::PUBLISHED);
        $censusMock->method('isWithinGracePeriod')->willReturn(true);

        $this->transactionManager->expects($this->once())->method('begin');
        $this->repository->expects($this->once())->method('delete')->with($censusMock);
        $this->repository->expects($this->never())->method('save');
        $this->transactionManager->expects($this->once())->method('commit');

        $this->handler->handle($request);
    }

    public function testItSoftDeletesPublishedCensusOutsideGracePeriod(): void
    {
        $request = new DeleteCensusRequest('8be4df61-93ca-11d2-aa0d-00e098032b8c');
        $censusMock = $this->createMock(Census::class);

        $this->repository->method('findById')->willReturn($censusMock);
        $censusMock->method('status')->willReturn(CensusStatus::PUBLISHED);
        $censusMock->method('isWithinGracePeriod')->willReturn(false);

        $censusMock->expects($this->once())->method('softDelete');

        $this->transactionManager->expects($this->once())->method('begin');
        $this->repository->expects($this->once())->method('save')->with($censusMock);
        $this->repository->expects($this->never())->method('delete');
        $this->transactionManager->expects($this->once())->method('commit');

        $this->handler->handle($request);
    }

    public function testItThrowsExceptionWhenCensusNotFound(): void
    {
        $request = new DeleteCensusRequest('8be4df61-93ca-11d2-aa0d-00e098032b8c');

        $this->repository->method('findById')->willReturn(null);

        $this->expectException(CensusNotFoundException::class);

        $this->handler->handle($request);
    }
}
