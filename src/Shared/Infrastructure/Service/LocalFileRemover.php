<?php
declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;
use App\Shared\Application\Service\FileRemoverInterface;
use App\Shared\Domain\ValueObject\AudioPath;
use App\Shared\Domain\ValueObject\ImagePath;

final readonly class LocalFileRemover implements FileRemoverInterface
{
    public function __construct(
        private string $publicDir
    ) {
    }

    public function remove(ImagePath|AudioPath $path): void
    {
        $fullPath = $this->publicDir . $path->value();

        if (file_exists($fullPath) && is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
