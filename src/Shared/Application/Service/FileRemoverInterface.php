<?php
declare(strict_types=1);

namespace App\Shared\Application\Service;

use App\Shared\Domain\ValueObject\AudioPath;
use App\Shared\Domain\ValueObject\ImagePath;

interface FileRemoverInterface
{
    public function remove(ImagePath|AudioPath $path): void;
}
