<?php

declare(strict_types=1);

namespace App\Census\Infrastructure\Persistence;

use App\Census\Domain\Entity\Census;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Census\Domain\ValueObject\CensusStatus;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCensusRepository implements CensusRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function findById(CensusId $id): ?Census
    {
        return $this->em->getRepository(Census::class)->findOneBy(['id' => $id->value()]);
    }

    public function findPaginated(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        return $this->em->createQueryBuilder()
            ->select('c')
            ->from(Census::class, 'c')
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string[] $censusIds
     * @return string[]
     */
    public function findCensusIdsByStatus(array $censusIds, CensusStatus $status): array {
        return $this->em->createQueryBuilder()
            ->select('c.id')
            ->from(Census::class, 'c')
            ->where('c.id IN (:censusIds)')
            ->andWhere('c.status = :status')
            ->setParameter('censusIds', $censusIds)
            ->setParameter('status', $status->value)
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function count(): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(c.id)')
            ->from(Census::class, 'c')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(Census $census): void
    {
        $this->em->persist($census);
    }

    public function delete(Census $census): void
    {
        $this->em->remove($census);
    }
}
