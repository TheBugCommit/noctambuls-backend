<?php

declare(strict_types=1);

namespace App\Shared\Application\DTO;

readonly class UploadFileRequest
{
    public function __construct(
        public string $tmpPath,
        public string $originalName,
        public string $extension,
        public int    $sizeInBytes
    )
    {
    }
}
