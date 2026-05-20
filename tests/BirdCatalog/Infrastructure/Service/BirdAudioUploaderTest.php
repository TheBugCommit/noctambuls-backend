<?php

declare(strict_types=1);

namespace App\Tests\BirdCatalog\Infrastructure\Service;

use App\BirdCatalog\Infrastructure\Service\BirdAudioUploader;
use App\Shared\Application\DTO\UploadFileRequest;
use PHPUnit\Framework\TestCase;

final class BirdAudioUploaderTest extends TestCase
{
    private const string AUDIOS_DIR = '/audios/birds';

    private string $tmpFile  = '';
    private string $uploadDir = '';

    protected function setUp(): void
    {
        $this->uploadDir = sys_get_temp_dir() . '/bird_audio_test_' . uniqid();
    }

    protected function tearDown(): void
    {
        if ($this->tmpFile !== '' && file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
        if (is_dir($this->uploadDir . self::AUDIOS_DIR)) {
            array_map('unlink', glob($this->uploadDir . self::AUDIOS_DIR . '/*.ogg') ?: []);
            rmdir($this->uploadDir . self::AUDIOS_DIR);
        }
    }

    public function test_throws_when_extension_is_not_ogg(): void
    {
        $uploader = new BirdAudioUploader(self::AUDIOS_DIR, $this->uploadDir);
        $file     = new UploadFileRequest('', 'test.mp3', 'mp3', 100);

        $this->expectException(\InvalidArgumentException::class);

        $uploader->uploadAudio($file);
    }

    public function test_throws_when_file_exceeds_400kb(): void
    {
        $uploader = new BirdAudioUploader(self::AUDIOS_DIR, $this->uploadDir);
        $file     = new UploadFileRequest('', 'test.ogg', 'ogg', 409601);

        $this->expectException(\InvalidArgumentException::class);

        $uploader->uploadAudio($file);
    }

    public function test_upload_returns_audio_path_with_ogg_extension(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'bird_ogg_');
        file_put_contents($this->tmpFile, 'fake-ogg-content');

        $uploader = new BirdAudioUploader(self::AUDIOS_DIR, $this->uploadDir);
        $file     = new UploadFileRequest($this->tmpFile, 'test.ogg', 'ogg', 100);

        $result = $uploader->uploadAudio($file);

        self::assertStringEndsWith('.ogg', $result->value());
        self::assertStringStartsWith(self::AUDIOS_DIR . '/', $result->value());

        $this->tmpFile = '';
    }

    public function test_upload_moves_file_to_target_directory(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'bird_ogg_');
        file_put_contents($this->tmpFile, 'fake-ogg-content');
        $originalPath = $this->tmpFile;

        $uploader = new BirdAudioUploader(self::AUDIOS_DIR, $this->uploadDir);
        $file     = new UploadFileRequest($this->tmpFile, 'test.ogg', 'ogg', 100);

        $result = $uploader->uploadAudio($file);

        self::assertFalse(file_exists($originalPath));
        self::assertTrue(file_exists($this->uploadDir . $result->value()));

        $this->tmpFile = '';
    }
}
