<?php

declare(strict_types=1);

namespace App\Observation\Infrastructure\Persistence;

use App\Auth\Domain\ValueObject\AuthUserId;
use App\Census\Domain\Entity\Census;
use App\Observation\Domain\Entity\Observation;
use App\Observation\Domain\Entity\Session;
use App\Profile\Domain\Entity\Profile;
use App\Observation\Domain\Filter\SessionFilter;
use App\Observation\Domain\Repository\SessionRepositoryInterface;
use App\Observation\Domain\ValueObject\ObservationId;
use App\Observation\Domain\ValueObject\SessionId;
use App\Observation\Domain\ValueObject\SessionType;
use App\Profile\Domain\ValueObject\OrnithologistCode;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
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

        return $qb->getQuery()->getSingleColumnResult();
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

    public function countForBackoffice(SessionFilter $filter): int
    {
        $qb = $this->applyFilterToQueryBuilder($filter);
        $qb->select('COUNT(DISTINCT s.id)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function findPaginatedIdsForBackoffice(SessionFilter $filter, int $page, int $limit): array
    {
        $qb = $this->applyFilterToQueryBuilder($filter);

        $qb->select('s.id')
            ->orderBy('s.startTime', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getSingleColumnResult();
    }

    public function findAllIdsForBackoffice(SessionFilter $filter): array
    {
        return $this->applyFilterToQueryBuilder($filter)
            ->select('s.id')
            ->orderBy('s.startTime', 'DESC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findPaginatedDeletedIds(int $page, int $limit): array
    {
        return $this->em->createQueryBuilder()
            ->select('s.id')
            ->from(Session::class, 's')
            ->where('s.isDeleted = true')
            ->orderBy('s.updatedAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult();
    }

    public function countDeleted(): int
    {
        return (int)$this->em->createQueryBuilder()
            ->select('COUNT(s.id)')
            ->from(Session::class, 's')
            ->where('s.isDeleted = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function purgeDeleted(): int
    {
        return (int) $this->em->createQuery(
            'DELETE FROM ' . Session::class . ' s WHERE s.isDeleted = true'
        )->execute();
    }

    public function findByObservationId(ObservationId $id): ?Session
    {
        return $this->em->createQueryBuilder()
            ->select('s', 'o', 'p')
            ->from(Session::class, 's')
            ->leftJoin('s.observations', 'o')
            ->leftJoin('s.participants', 'p')
            ->where('o.id = :id')
            ->setParameter('id', $id->value())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Session $session): void
    {
        $this->em->persist($session);
    }

    public function free(Session $session): void
    {
        $this->em->detach($session);
    }

    private function applyFilterToQueryBuilder(SessionFilter $filter): QueryBuilder
    {
        $qb = $this->em->createQueryBuilder()
            ->from(Session::class, 's')
            ->where('s.isDeleted = :isDeleted')
            ->setParameter('isDeleted', $filter->isDeleted);

        if ($filter->sessionId !== null) {
            $qb->andWhere('s.id LIKE :sessionId')->setParameter('sessionId', '%' . $filter->sessionId . '%');
        }
        if ($filter->sessionType !== null) {
            $qb->andWhere('s.sessionType = :sessionType')->setParameter('sessionType', $filter->sessionType);
        }
        if ($filter->dateFrom !== null) {
            $qb->andWhere('s.startTime >= :dateFrom')->setParameter('dateFrom', $filter->dateFrom, Types::DATETIME_IMMUTABLE);
        }
        if ($filter->dateTo !== null) {
            $qb->andWhere('s.startTime <= :dateTo')->setParameter('dateTo', $filter->dateTo, Types::DATETIME_IMMUTABLE);
        }
        if ($filter->hasObservations !== null) {
            $subQb = $this->em->createQueryBuilder()
                ->select('1')
                ->from(Observation::class, 'obs')
                ->where('obs.session = s')
                ->andWhere('obs.isDeleted = false');

            if ($filter->hasObservations === true) {
                $qb->andWhere($qb->expr()->exists($subQb->getDQL()));
            } else {
                $qb->andWhere($qb->expr()->not($qb->expr()->exists($subQb->getDQL())));
            }
        }
        if ($filter->censusId !== null) {
            $qb->andWhere('s.censusId LIKE :censusId')->setParameter('censusId', '%' . $filter->censusId . '%');
        }
        if ($filter->ornithologistCode !== null) {
            $subQb = $this->em->createQueryBuilder()
                ->select('prof.id')
                ->from(Profile::class, 'prof')
                ->where('prof.ornithologistCode LIKE :ornithologistCode');
            $qb->andWhere($qb->expr()->in('s.annotatorId', $subQb->getDQL()))
               ->setParameter('ornithologistCode', '%' . $filter->ornithologistCode . '%');
        }
        if ($filter->listeningPoint !== null) {
            $qb->andWhere('s.listeningPoint = :listeningPoint')
               ->setParameter('listeningPoint', $filter->listeningPoint);
        }
        if ($filter->censusTitle !== null) {
            $subQb = $this->em->createQueryBuilder()
                ->select('c.id')
                ->from(Census::class, 'c')
                ->where('c.title LIKE :censusTitle');
            $qb->andWhere($qb->expr()->in('s.censusId', $subQb->getDQL()))
               ->setParameter('censusTitle', '%' . $filter->censusTitle . '%');
        }

        return $qb;
    }
}
