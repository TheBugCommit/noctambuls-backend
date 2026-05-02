<?php

namespace App\Profile\Domain\Service;

use App\Profile\Domain\ValueObject\OrnithologistCode;
use App\Profile\Domain\ValueObject\ProfileName;

interface OrnithologistCodeGeneratorInterface
{
    public function generate(ProfileName $name): OrnithologistCode;
}
