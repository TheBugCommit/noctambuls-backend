<?php

declare(strict_types=1);

namespace App\Tests\BirdCatalog\Infrastructure\Service;

use App\BirdCatalog\Infrastructure\Service\BirdImageUploader;
use App\Shared\Application\DTO\UploadFileRequest;
use PHPUnit\Framework\TestCase;

final class BirdImageUploaderTest extends TestCase
{
    private const string IMAGES_DIR = '/uploads/birds/images';

    private string $tmpFile   = '';
    private string $uploadDir = '';

    protected function setUp(): void
    {
        $this->uploadDir = \sys_get_temp_dir() . '/bird_img_test_' . uniqid();
    }

    protected function tearDown(): void
    {
        if ($this->tmpFile !== '' && file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
        $targetDir = $this->uploadDir . self::IMAGES_DIR;
        if (is_dir($targetDir)) {
            array_map('unlink', glob($targetDir . '/*.webp') ?: []);
            rmdir($targetDir);
        }
    }

    public function test_upload_returns_image_path_when_width_is_exactly_1500(): void
    {
        $this->tmpFile = $this->createTempWebp(1500, 1000);

        $uploader = new BirdImageUploader(self::IMAGES_DIR, $this->uploadDir);
        $file     = new UploadFileRequest($this->tmpFile, 'test.webp', 'webp', 1000);

        $result = $uploader->uploadImage($file);

        self::assertStringEndsWith('.webp', $result->value());
        self::assertStringStartsWith(self::IMAGES_DIR . '/', $result->value());

        $this->tmpFile = '';
    }

    public function test_upload_resizes_and_returns_image_path_when_width_exceeds_1500(): void
    {
        $this->tmpFile = $this->createTempWebp(2000, 1333);

        $uploader = new BirdImageUploader(self::IMAGES_DIR, $this->uploadDir);
        $file     = new UploadFileRequest($this->tmpFile, 'test.webp', 'webp', 1000);

        $result = $uploader->uploadImage($file);

        self::assertStringEndsWith('.webp', $result->value());
        self::assertTrue(file_exists($this->uploadDir . $result->value()));

        $this->tmpFile = '';
    }

    private function createTempWebp(int $width, int $height): string
    {
        $img  = \imagecreatetruecolor($width, $height);
        $path = \tempnam(\sys_get_temp_dir(), 'bird_img_') . '.webp';
        \imagewebp($img, $path, 90);
        \imagedestroy($img);
        return $path;
    }
}
