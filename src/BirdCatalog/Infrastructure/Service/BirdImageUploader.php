<?php
declare(strict_types=1);

namespace App\BirdCatalog\Infrastructure\Service;

use App\Shared\Application\DTO\UploadFileRequest;
use App\Shared\Application\Service\ImageUploaderInterface;
use App\Shared\Domain\ValueObject\ImagePath;
use InvalidArgumentException;

final readonly class BirdImageUploader implements ImageUploaderInterface
{
    public function __construct(
        private string $imagesDir = '/uploads/birds/images',
        private string $publicDir
    ) {
    }

    public function uploadImage(UploadFileRequest $file): ImagePath
    {
        try {
            $extension = strtolower($file->extension);
            if ($extension !== 'webp') {
                throw new InvalidArgumentException('La imatge ha de ser en format WebP.');
            }

            $imageInfo = getimagesize($file->tmpPath);
            if ($imageInfo === false) {
                throw new InvalidArgumentException('El fitxer no és una imatge vàlida.');
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];

            if ($width < 1500) {
                throw new InvalidArgumentException(sprintf('La imatge ha de tenir com a mínim 1500px d\'amplada (actual: %dpx).', $width));
            }

            $fileName = md5(uniqid()) . '.webp';
            $targetDirectory = $this->publicDir . $this->imagesDir;
            $targetPath = $targetDirectory . '/' . $fileName;

            if (!is_dir($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }

            if ($width > 1500) {
                $newWidth = 1500;
                $newHeight = (int) ($height * ($newWidth / $width));

                $source = imagecreatefromwebp($file->tmpPath);
                $destination = imagecreatetruecolor($newWidth, $newHeight);

                imagealphablending($destination, false);
                imagesavealpha($destination, true);
                imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagewebp($destination, $targetPath, 90);

                imagedestroy($source);
                imagedestroy($destination);
            } else {
                rename($file->tmpPath, $targetPath);
            }

            if (file_exists($targetPath)) {
                chmod($targetPath, 0644);
            }

            return new ImagePath($this->imagesDir . '/' . $fileName);
        } catch (\Throwable $e) {
            dd($e);
        }

    }
}
