<?php

declare(strict_types=1);
namespace App\Shared\Application\Service;

use App\Shared\Application\DTO\UploadFileRequest;
use App\Shared\Domain\ValueObject\AudioPath;

interface AudioUploaderInterface
{
    public function uploadAudio(UploadFileRequest $file): AudioPath;
}
