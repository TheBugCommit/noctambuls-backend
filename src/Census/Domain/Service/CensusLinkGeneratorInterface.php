<?php

declare(strict_types=1);

namespace App\Census\Domain\Service;

use App\Census\Domain\Entity\Census;

interface CensusLinkGeneratorInterface
{
    public function generateFor(Census $census): string;
}
