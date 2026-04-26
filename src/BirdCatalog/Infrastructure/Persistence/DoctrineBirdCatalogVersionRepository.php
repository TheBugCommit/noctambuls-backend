<?php

declare(strict_types=1);

namespace App\BirdCatalog\Infrastructure\Persistence;

use App\BirdCatalog\Domain\Entity\BirdCatalogVersion;
use App\BirdCatalog\Domain\Repository\BirdCatalogVersionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineBirdCatalogVersionRepository implements BirdCatalogVersionRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function get(): BirdCatalogVersion
    {
        return $this->em->getRepository(BirdCatalogVersion::class)->find(1);
    }

    public function save(BirdCatalogVersion $version): void
    {
        $this->em->persist($version);
    }
}
