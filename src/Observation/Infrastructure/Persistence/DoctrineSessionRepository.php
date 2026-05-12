<?php

declare(strict_types=1);

namespace App\Observation\Infrastructure\Persistence;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\SessionId;
use App\Profile\Domain\ValueObject\OrnithologistCode;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

final readonly class DoctrineSessionRepository implements SessionRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function findById(SessionId $id): ?Session
    {
        return $this->em->createQueryBuilder()
            ->select('s', 'o')
            ->from(Session::class, 's')
            ->leftJoin('s.observations', 'o')
            ->where('s.id = :id')
            ->setParameter('id', $id->value())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByIds(array $sessionIds): array
    {
        if (empty($sessionIds)) {
            return [];
        }

        return $this->em->createQueryBuilder()
            ->select('s', 'o')
            ->from(Session::class, 's')
            ->leftJoin('s.observations', 'o')
            ->where('s.id IN (:ids)')
            ->setParameter('ids', $sessionIds)
            ->getQuery()
            ->getResult();
    }

    public function findPaginatedIdsByAnnotatorId(AuthUserId $annotatorId, int $offset, int $limit, ?\DateTimeImmutable $since = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('s.id')
            ->from(Session::class, 's')
            ->where('s.annotatorId = :annotatorId')
            ->setParameter('annotatorId', $annotatorId->value())
            ->orderBy('s.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($since !== null) {
            $qb->andWhere('s.updatedAt > :since')
               ->setParameter('since', $since, Types::DATETIME_IMMUTABLE);
        }

        return $qb->getQuery()->getScalarResult();
    }

    /**
     * @param string[] $censusIds
     * @return Session[]
     */
    public function findByCensusIdsAndAnnotator(array $censusIds, AuthUserId $annotatorId): array
    {
        if (empty($censusIds)) {
            return [];
        }

        return $this->em->createQueryBuilder()
            ->select('s')
            ->from(Session::class, 's')
            ->where('s.censusId IN (:censusIds)')
            ->andWhere('s.annotatorId = :annotatorId')
            ->setParameter('censusIds', $censusIds)
            ->setParameter('annotatorId', $annotatorId->value())
            ->getQuery()
            ->getResult();
    }

    public function countByAnnotatorId(AuthUserId $annotatorId, ?\DateTimeImmutable $since = null): int
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(s.id)')
            ->from(Session::class, 's')
            ->where('s.annotatorId = :annotatorId')
            ->setParameter('annotatorId', $annotatorId->value());

        if ($since !== null) {
            $qb->andWhere('s.updatedAt > :since')
               ->setParameter('since', $since, Types::DATETIME_IMMUTABLE);
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function countByParticipantCode(OrnithologistCode $code, ?\DateTimeImmutable $since = null): int
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(DISTINCT s.id)')
            ->from(Session::class, 's')
            ->join('s.participants', 'p')
            ->where('p.ornithologistCode = :code')
            ->setParameter('code', $code->value());

        if ($since !== null) {
            $qb->andWhere('s.updatedAt > :since')
               ->setParameter('since', $since, Types::DATETIME_IMMUTABLE);
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function findPaginatedIdsByParticipantCode(OrnithologistCode $code, int $offset, int $limit, ?\DateTimeImmutable $since = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('DISTINCT s.id')
            ->from(Session::class, 's')
            ->join('s.participants', 'p')
            ->where('p.ornithologistCode = :code')
            ->setParameter('code', $code->value())
            ->orderBy('s.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($since !== null) {
            $qb->andWhere('s.updatedAt > :since')
               ->setParameter('since', $since, Types::DATETIME_IMMUTABLE);
        }

        return $qb->getQuery()->getScalarResult();
    }

    public function findByIdsWithRelations(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->em->createQueryBuilder()
            ->select('s', 'o', 'p')
            ->from(Session::class, 's')
            ->leftJoin('s.observations', 'o')
            ->leftJoin('s.participants', 'p')
            ->where('s.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(Session $session): void
    {
        $this->em->persist($session);
    }

    public function free(Session $session): void
    {
        $this->em->detach($session);
    }
}
