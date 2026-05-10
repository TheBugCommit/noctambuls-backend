<?php

declare(strict_types=1);

namespace App\Census\Application\CreateCensus;

use App\Census\Application\Mapper\CensusResponseMapper;
use App\Census\Domain\Entity\Census;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusDescription;
use App\Census\Domain\ValueObject\CensusId;
use App\Census\Domain\ValueObject\CensusLocationName;
use App\Census\Domain\ValueObject\CensusTitle;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use Throwable;

final readonly class CreateCensusHandler
{
    public function __construct(
        private CensusRepositoryInterface  $censusRepository,
        private UuidGeneratorInterface     $uuidGenerator,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    /** @throws Throwable */
    public function handle(CreateCensusRequest $request): CreateCensusResponse
    {
        $id = new CensusId($this->uuidGenerator->generate());

        $census = Census::create(
            $id,
            new CensusTitle($request->title),
            new \DateTimeImmutable($request->date),
            new \DateTimeImmutable($request->time),
            new CensusLocationName($request->locationName),
            $request->description !== null && $request->description !== ''
                ? new CensusDescription($request->description)
                : null,
        );

        $this->transactionManager->begin();
        try {
            $this->censusRepository->save($census);
            $this->transactionManager->commit();
        } catch (Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }

        return CensusResponseMapper::toCreateCensusResponse($id);
    }
}
