<?php

declare(strict_types=1);

namespace App\Profile\Infrastructure\Persistence;

use App\Profile\Domain\Entity\Profile;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\OrnithologistCode;
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

    public function findMaxNumberForPrefix(string $prefix): int
    {
        $result = $this->em->createQueryBuilder()
            ->select('MAX(p.ornithologistCode)')
            ->from(Profile::class, 'p')
            ->where('p.ornithologistCode LIKE :prefix')
            ->setParameter('prefix', $prefix . '%')
            ->getQuery()
            ->getSingleScalarResult();

        if ($result === null) {
            return 0;
        }

        $numberString = substr($result, -4);
        return (int) $numberString;
    }


    public function save(Profile $profile): void
    {
        $this->em->persist($profile);
    }
}
