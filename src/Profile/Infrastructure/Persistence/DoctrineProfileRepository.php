<?php

declare(strict_types=1);

namespace App\Profile\Infrastructure\Persistence;

use App\Profile\Domain\Entity\Profile;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\ProfileId;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProfileRepository implements ProfileRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function findById(ProfileId $id): ?Profile
    {
        return $this->em->getRepository(Profile::class)->findOneBy([
            'id' => $id->value(),
        ]);
    }

    public function save(Profile $profile): void
    {
        $this->em->persist($profile);
    }
}
