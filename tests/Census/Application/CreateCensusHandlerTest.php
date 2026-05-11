<?php

declare(strict_types=1);

namespace App\Tests\Census\Application;

use App\Census\Domain\Entity\Census;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Census\Application\CreateCensus\CreateCensusHandler;
use App\Census\Application\CreateCensus\CreateCensusRequest;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;

final class CreateCensusHandlerTest extends TestCase
{
    private CensusRepositoryInterface&MockObject $repository;
    private UuidGeneratorInterface&MockObject $uuidGenerator;
    private TransactionManagerInterface&MockObject $transactionManager;
    private CreateCensusHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CensusRepositoryInterface::class);
        $this->uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->handler = new CreateCensusHandler(
            $this->repository,
            $this->uuidGenerator,
            $this->transactionManager
        );
    }

    public function testItCreatesACensusSuccessfully(): void
    {
        $request = new CreateCensusRequest(
            title: 'Cens d\'Ocells',
            date: '2026-05-11',
            time: '15:30',
            latitude: 41.5819,
            longitude: 1.6177,
            locationName: 'Igualada',
            description: 'Descripció de prova'
        );

        $this->uuidGenerator->expects($this->once())->method('generate')->willReturn('8be4df61-93ca-11d2-aa0d-00e098032b8c');
        $this->transactionManager->expects($this->once())->method('begin');
        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Census::class));
        $this->transactionManager->expects($this->once())->method('commit');

        $response = $this->handler->handle($request);

        $this->assertEquals('8be4df61-93ca-11d2-aa0d-00e098032b8c', $response->id);
    }

    public function testItRollbacksTransactionWhenRepositoryFails(): void
    {
        $request = new CreateCensusRequest('Títol', '2026-05-11', '15:30', 41.0, 1.0, 'Loc', null);

        $this->uuidGenerator->method('generate')->willReturn('8be4df61-93ca-11d2-aa0d-00e098032b8c');

        $this->transactionManager->expects($this->once())->method('begin');
        $this->repository->method('save')->willThrowException(new \RuntimeException('DB Error'));

        $this->transactionManager->expects($this->once())->method('rollback');
        $this->transactionManager->expects($this->never())->method('commit');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle($request);
    }

    public function testItThrowsExceptionOnInvalidValueObjectsData(): void
    {
        $request = new CreateCensusRequest(
            title: 'Títol',
            date: '2026-05-11',
            time: '15:30',
            latitude: 150.0,
            longitude: 1.6177,
            locationName: 'Igualada',
            description: null
        );

        $this->transactionManager->expects($this->never())->method('begin');
        $this->repository->expects($this->never())->method('save');

        $this->expectException(\InvalidArgumentException::class);

        $this->handler->handle($request);
    }

    public function testItThrowsExceptionOnInvalidDate(): void
    {
        $request = new CreateCensusRequest(
            title: 'Títol',
            date: '',
            time: '15:30',
            latitude: 150.0,
            longitude: 1.6177,
            locationName: 'Igualada',
            description: null
        );

        $this->transactionManager->expects($this->never())->method('begin');
        $this->repository->expects($this->never())->method('save');

        $this->expectException(\InvalidArgumentException::class);

        $this->handler->handle($request);
    }

    public function testItThrowsExceptionOnInvalidTime(): void
    {
        $request = new CreateCensusRequest(
            title: 'Títol',
            date: '2026-05-11',
            time: '15:s30',
            latitude: 50.0,
            longitude: 1.6177,
            locationName: 'Igualada',
            description: null
        );

        $this->transactionManager->expects($this->never())->method('begin');
        $this->repository->expects($this->never())->method('save');

        $this->expectException(\InvalidArgumentException::class);

        $this->handler->handle($request);
    }

    public function testItThrowsExceptionOnInvalidLat(): void
    {
        $request = new CreateCensusRequest(
            title: 'Títol',
            date: '2026-05-11',
            time: '15:30',
            latitude: 5000.0,
            longitude: 1.6177,
            locationName: 'Igualada',
            description: null
        );

        $this->transactionManager->expects($this->never())->method('begin');
        $this->repository->expects($this->never())->method('save');

        $this->expectException(\InvalidArgumentException::class);

        $this->handler->handle($request);
    }

    public function testItThrowsExceptionOnInvalidLatName(): void
    {
        $request = new CreateCensusRequest(
            title: 'Títol',
            date: '2026-05-11',
            time: '15:30',
            latitude: 50.0,
            longitude: 1.6177,
            locationName: '',
            description: null
        );

        $this->transactionManager->expects($this->never())->method('begin');
        $this->repository->expects($this->never())->method('save');

        $this->expectException(\InvalidArgumentException::class);

        $this->handler->handle($request);
    }
}
