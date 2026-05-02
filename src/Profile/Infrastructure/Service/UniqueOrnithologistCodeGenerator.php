<?php

namespace App\Profile\Infrastructure\Service;

use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\Service\OrnithologistCodeGeneratorInterface;
use App\Profile\Domain\ValueObject\OrnithologistCode;
use App\Profile\Domain\ValueObject\ProfileName;

final readonly class UniqueOrnithologistCodeGenerator implements OrnithologistCodeGeneratorInterface
{
    public function __construct(
        private ProfileRepositoryInterface $repository
    ) {}

    public function generate(ProfileName $name): OrnithologistCode
    {
        $prefix = OrnithologistCode::extractPrefixFromName($name);

        $maxExistingNumber = $this->repository->findMaxNumberForPrefix($prefix);

        $nextNumber = $maxExistingNumber + 1;

        if ($nextNumber > 9999) {
            throw new \RuntimeException('S\'han exhaurit els codis per aquestes inicials.');
        }

        return OrnithologistCode::generate($name, $nextNumber);
    }
}
