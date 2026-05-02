<?php

namespace App\Profile\Application\Service;

use App\Profile\Domain\Entity\Profile;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\Service\OrnithologistCodeGeneratorInterface;
use App\Profile\Domain\ValueObject\PhoneNumber;
use App\Profile\Domain\ValueObject\ProfileId;
use App\Profile\Domain\ValueObject\ProfileName;

final readonly class CreateUserProfileService
{
    public function __construct(
        private ProfileRepositoryInterface          $repository,
        private OrnithologistCodeGeneratorInterface $codeGenerator
    )
    {
    }

    public function execute(string $userId, string $nameString, string $phone): void
    {
        $name = new ProfileName($nameString);
        $code = $this->codeGenerator->generate($name);
        $profile = Profile::create(
            new ProfileId($userId),
            $name,
            new PhoneNumber($phone),
            $code
        );
        $this->repository->save($profile);
    }
}
