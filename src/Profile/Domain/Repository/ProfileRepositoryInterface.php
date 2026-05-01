<?php

declare(strict_types=1);

namespace App\Profile\Domain\Repository;

use App\Profile\Domain\Entity\Profile;
use App\Profile\Domain\ValueObject\ProfileId;

interface ProfileRepositoryInterface
{
    public function findById(ProfileId $id): ?Profile;

    public function save(Profile $profile): void;
}
