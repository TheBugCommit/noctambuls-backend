<?php

declare(strict_types=1);

namespace App\Tests\BirdCatalog\Application\CreateBird;

use App\BirdCatalog\Application\BirdTranslation\BirdTranslationRequest;
use App\BirdCatalog\Application\CreateBird\CreateBirdHandler;
use App\BirdCatalog\Application\CreateBird\CreateBirdRequest;
use App\BirdCatalog\Application\Service\BirdCatalogUpdaterService;
use App\BirdCatalog\Application\Service\BirdService;
use App\BirdCatalog\Domain\Entity\Bird;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\BirdCatalog\Domain\ValueObject\InformationUrl;
use App\BirdCatalog\Domain\ValueObject\LanguageCode;
use App\BirdCatalog\Domain\ValueObject\ScientificName;
use App\Shared\Application\DTO\UploadFileRequest;
use App\Shared\Application\Service\AudioUploaderInterface;
use App\Shared\Application\Service\FileRemoverInterface;
use App\Shared\Application\Service\ImageUploaderInterface;
use App\Shared\Domain\ValueObject\AudioPath;
use App\Shared\Domain\ValueObject\ImagePath;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateBirdHandlerTest extends TestCase
{
    private const string BIRD_UUID = '550e8400-e29b-41d4-a716-446655440001';

    private ImageUploaderInterface&MockObject    $imageUploader;
    private AudioUploaderInterface&MockObject    $audioUploader;
    private FileRemoverInterface&MockObject      $fileRemover;
    private BirdService&MockObject               $birdService;
    private BirdCatalogUpdaterService&MockObject $updaterService;
    private CreateBirdHandler                    $handler;

    private ImagePath $imagePath;
    private AudioPath $audioPath;

    protected function setUp(): void
    {
        $this->imageUploader  = $this->createMock(ImageUploaderInterface::class);
        $this->audioUploader  = $this->createMock(AudioUploaderInterface::class);
        $this->fileRemover    = $this->createMock(FileRemoverInterface::class);
        $this->birdService    = $this->createMock(BirdService::class);
        $this->updaterService = $this->createMock(BirdCatalogUpdaterService::class);
        $this->handler        = new CreateBirdHandler(
            $this->imageUploader,
            $this->audioUploader,
            $this->fileRemover,
            $this->birdService,
            $this->updaterService,
        );

        $this->imagePath = new ImagePath('/img/test.webp');
        $this->audioPath = new AudioPath('/audio/test.ogg');

        $this->imageUploader->method('uploadImage')->willReturn($this->imagePath);
        $this->audioUploader->method('uploadAudio')->willReturn($this->audioPath);
        $this->birdService->method('create')->willReturn($this->createBird());
    }

    public function test_handle_creates_bird_and_returns_response_with_id(): void
    {
        $this->updaterService->expects(self::once())->method('save');

        $response = $this->handler->handle($this->makeRequest());

        self::assertSame(self::BIRD_UUID, $response->id);
    }

    public function test_handle_does_not_call_file_remover_when_image_upload_fails(): void
    {
        $this->imageUploader->method('uploadImage')->willThrowException(new \RuntimeException('Upload failed'));
        $this->fileRemover->expects(self::never())->method('remove');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle($this->makeRequest());
    }

    public function test_handle_removes_uploaded_image_when_audio_upload_fails(): void
    {
        $this->audioUploader->method('uploadAudio')->willThrowException(new \RuntimeException('Audio failed'));
        $this->fileRemover->expects(self::once())->method('remove')->with($this->imagePath);

        $this->expectException(\RuntimeException::class);

        $this->handler->handle($this->makeRequest());
    }

    public function test_handle_removes_both_files_when_bird_service_throws(): void
    {
        $this->birdService->method('create')->willThrowException(new \RuntimeException('Service failed'));
        $this->fileRemover->expects(self::exactly(2))->method('remove');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle($this->makeRequest());
    }

    private function makeRequest(): CreateBirdRequest
    {
        $file = new UploadFileRequest('/tmp/file', 'file.webp', 'webp', 1000);
        return new CreateBirdRequest(
            scientificName: 'Turdus merula',
            image: $file,
            audio: new UploadFileRequest('/tmp/audio', 'audio.ogg', 'ogg', 1000),
            informationUrl: 'https://example.com',
            translations: [
                new BirdTranslationRequest(LanguageCode::CA, 'Merla', 'Ocell'),
                new BirdTranslationRequest(LanguageCode::ES, 'Mirlo', 'Pájaro'),
            ],
        );
    }

    private function createBird(): Bird
    {
        return Bird::create(
            new BirdId(self::BIRD_UUID),
            new ScientificName('Turdus merula'),
            new ImagePath('/img/test.webp'),
            new AudioPath('/audio/test.ogg'),
            new InformationUrl('https://example.com'),
        );
    }
}
