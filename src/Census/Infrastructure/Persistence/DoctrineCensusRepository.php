<?php

declare(strict_types=1);

namespace App\Census\Infrastructure\Persistence;

use App\Census\Domain\Entity\Census;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Census\Domain\ValueObject\CensusStatus;
use Doctrine\DBAL\Types\Types;
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
    /**
     * @param string[] $censusIds
     * @return Census[]
     */
    public function findByIds(array $censusIds): array
    {
        if (empty($censusIds)) {
            return [];
        }

        return $this->em->createQueryBuilder()
            ->select('c')
            ->from(Census::class, 'c')
            ->where('c.id IN (:censusIds)')
            ->setParameter('censusIds', $censusIds)
            ->getQuery()
            ->getResult();
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

    public function findPublishedUpcoming(?\DateTimeImmutable $since = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('c')
            ->from(Census::class, 'c')
            ->where('c.status = :status')
            ->andWhere('c.date >= :today')
            ->setParameter('status', CensusStatus::PUBLISHED->value)
            ->setParameter('today', new \DateTimeImmutable('today', new \DateTimeZone('UTC')), Types::DATE_IMMUTABLE)
            ->orderBy('c.date', 'ASC')
            ->addOrderBy('c.time', 'ASC');

        if ($since !== null) {
            $qb->andWhere('c.updatedAt > :since')
               ->setParameter('since', $since, Types::DATETIME_IMMUTABLE);
        }

        return $qb->getQuery()->getResult();
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
