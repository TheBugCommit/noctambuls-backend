<?php

declare(strict_types=1);

namespace App\Shared\Application\Service;

interface QrCodeGeneratorInterface
{
    public function generateBase64(string $data): string;
}
