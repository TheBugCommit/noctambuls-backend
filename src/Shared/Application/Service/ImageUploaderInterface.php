<?php

declare(strict_types=1);
namespace App\Shared\Application\Service;

use App\Shared\Application\DTO\UploadFileRequest;
use App\Shared\Domain\ValueObject\ImagePath;

interface ImageUploaderInterface
{
    public function uploadImage(UploadFileRequest $file): ImagePath;
}
