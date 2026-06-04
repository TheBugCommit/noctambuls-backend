<?php
declare(strict_types=1);

namespace App\BirdCatalog\Infrastructure\Service;

use App\Shared\Application\DTO\UploadFileRequest;
use App\Shared\Application\Service\AudioUploaderInterface;
use App\Shared\Domain\ValueObject\AudioPath;
use InvalidArgumentException;

final readonly class BirdAudioUploader implements AudioUploaderInterface
{
    public function __construct(
        private string $audiosDir,
        private string $publicDir
    ) {
    }
    public function uploadAudio(UploadFileRequest $file): AudioPath
    {
        $extension = strtolower($file->extension);
        if ($extension !== 'ogg') {
            throw new InvalidArgumentException('L\'àudio ha de ser en format OGG.');
        }

        if ($file->sizeInBytes > 409600) {
            throw new InvalidArgumentException('L\'àudio no pot pesar més de 400KB.');
        }

        $fileName = md5(uniqid()) . '.ogg';
        $targetDirectory = $this->publicDir . $this->audiosDir;
        $targetPath = $targetDirectory . '/' . $fileName;

        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        rename($file->tmpPath, $targetPath);

        if (file_exists($targetPath)) {
            chmod($targetPath, 0644);
        }

        return new AudioPath($this->audiosDir . '/' . $fileName);
    }
}
