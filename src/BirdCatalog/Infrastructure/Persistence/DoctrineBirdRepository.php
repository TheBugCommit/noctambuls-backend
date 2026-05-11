<?php

declare(strict_types=1);

namespace App\BirdCatalog\Infrastructure\Persistence;

use App\BirdCatalog\Domain\Entity\Bird;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineBirdRepository implements BirdRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function findAll(): array
    {
        return $this->em->createQueryBuilder()
            ->select('b', 't')
            ->from(Bird::class, 'b')
            ->leftJoin('b.translations', 't')
            ->getQuery()
            ->getResult();
    }

    public function findById(BirdId $id): ?Bird
    {
        return $this->em->createQueryBuilder()
            ->select('b', 't')
            ->from(Bird::class, 'b')
            ->leftJoin('b.translations', 't')
            ->where('b.id = :id')
            ->setParameter('id', $id->value())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string[] $birdIds
     * @return string[]
     */
    public function findValidBirdIds(array $birdIds): array
    {
        if (empty($birdIds)) {
            return [];
        }

        $results = $this->em->createQueryBuilder()
            ->select('b.id')
            ->from(Bird::class, 'b')
            ->where('b.id IN (:ids)')
            ->setParameter('ids', $birdIds)
            ->getQuery()
            ->getScalarResult();

        return array_column($results, 'id');
    }


    public function save(Bird $bird): void
    {
        $this->em->persist($bird);
    }

    public function delete(Bird $bird): void
    {
        $this->em->remove($bird);
    }
}
