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
        return $this->em->getRepository(Bird::class)->findAll();
    }

    public function findById(BirdId $id): ?Bird
    {
        return $this->em->getRepository(Bird::class)->findOneBy(['id' => $id->value()]);
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
