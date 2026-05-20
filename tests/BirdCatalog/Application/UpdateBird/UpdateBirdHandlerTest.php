<?php

declare(strict_types=1);

namespace App\Tests\BirdCatalog\Application\UpdateBird;

use App\BirdCatalog\Application\BirdTranslation\BirdTranslationRequest;
use App\BirdCatalog\Application\Service\BirdCatalogUpdaterService;
use App\BirdCatalog\Application\UpdateBird\UpdateBirdHandler;
use App\BirdCatalog\Application\UpdateBird\UpdateBirdRequest;
use App\BirdCatalog\Domain\Entity\Bird;
use App\BirdCatalog\Domain\Entity\BirdTranslation;
use App\BirdCatalog\Domain\Exception\BirdNotFoundException;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\BirdCatalog\Domain\ValueObject\BirdDescription;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\BirdCatalog\Domain\ValueObject\BirdTranslationId;
use App\BirdCatalog\Domain\ValueObject\CommonName;
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

final class UpdateBirdHandlerTest extends TestCase
{
    private const string BIRD_UUID = '550e8400-e29b-41d4-a716-446655440001';

    private BirdRepositoryInterface&MockObject    $birdRepository;
    private ImageUploaderInterface&MockObject     $imageUploader;
    private AudioUploaderInterface&MockObject     $audioUploader;
    private FileRemoverInterface&MockObject       $fileRemover;
    private BirdCatalogUpdaterService&MockObject  $updaterService;
    private UpdateBirdHandler                     $handler;

    protected function setUp(): void
    {
        $this->birdRepository = $this->createMock(BirdRepositoryInterface::class);
        $this->imageUploader  = $this->createMock(ImageUploaderInterface::class);
        $this->audioUploader  = $this->createMock(AudioUploaderInterface::class);
        $this->fileRemover    = $this->createMock(FileRemoverInterface::class);
        $this->updaterService = $this->createMock(BirdCatalogUpdaterService::class);
        $this->handler        = new UpdateBirdHandler(
            $this->birdRepository,
            $this->imageUploader,
            $this->audioUploader,
            $this->fileRemover,
            $this->updaterService,
        );
    }

    public function test_handle_throws_when_bird_not_found(): void
    {
        $this->birdRepository->method('findById')->willReturn(null);

        $this->expectException(BirdNotFoundException::class);

        $this->handler->handle($this->makeRequest(image: null, audio: null));
    }

    public function test_handle_updates_bird_without_new_files_when_none_provided(): void
    {
        $this->birdRepository->method('findById')->willReturn($this->createBirdWithTranslations());

        $this->imageUploader->expects(self::never())->method('uploadImage');
        $this->audioUploader->expects(self::never())->method('uploadAudio');
        $this->fileRemover->expects(self::never())->method('remove');
        $this->updaterService->expects(self::once())->method('save');

        $this->handler->handle($this->makeRequest(image: null, audio: null));
    }

    public function test_handle_uploads_new_image_removes_old_on_success(): void
    {
        $bird           = $this->createBirdWithTranslations();
        $oldImagePath   = $bird->imagePath();
        $newImagePath   = new ImagePath('/img/new.webp');

        $this->birdRepository->method('findById')->willReturn($bird);
        $this->imageUploader->method('uploadImage')->willReturn($newImagePath);

        $this->fileRemover->expects(self::once())->method('remove')->with($oldImagePath);

        $this->handler->handle($this->makeRequest(
            image: new UploadFileRequest('/tmp/new.webp', 'new.webp', 'webp', 1000),
            audio: null,
        ));
    }

    public function test_handle_uploads_new_audio_removes_old_on_success(): void
    {
        $bird         = $this->createBirdWithTranslations();
        $oldAudioPath = $bird->audioPath();
        $newAudioPath = new AudioPath('/audio/new.ogg');

        $this->birdRepository->method('findById')->willReturn($bird);
        $this->audioUploader->method('uploadAudio')->willReturn($newAudioPath);

        $this->fileRemover->expects(self::once())->method('remove')->with($oldAudioPath);

        $this->handler->handle($this->makeRequest(
            image: null,
            audio: new UploadFileRequest('/tmp/new.ogg', 'new.ogg', 'ogg', 1000),
        ));
    }

    public function test_handle_removes_new_files_on_exception(): void
    {
        $newImagePath = new ImagePath('/img/new.webp');
        $newAudioPath = new AudioPath('/audio/new.ogg');

        $this->birdRepository->method('findById')->willReturn($this->createBirdWithTranslations());
        $this->imageUploader->method('uploadImage')->willReturn($newImagePath);
        $this->audioUploader->method('uploadAudio')->willReturn($newAudioPath);
        $this->updaterService->method('save')->willThrowException(new \RuntimeException('DB error'));

        $this->fileRemover->expects(self::exactly(2))->method('remove');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle($this->makeRequest(
            image: new UploadFileRequest('/tmp/img.webp', 'img.webp', 'webp', 1000),
            audio: new UploadFileRequest('/tmp/audio.ogg', 'audio.ogg', 'ogg', 1000),
        ));
    }

    private function makeRequest(?UploadFileRequest $image, ?UploadFileRequest $audio): UpdateBirdRequest
    {
        return new UpdateBirdRequest(
            id: self::BIRD_UUID,
            scientificName: 'Turdus merula',
            informationUrl: 'https://example.com',
            translations: [
                new BirdTranslationRequest(LanguageCode::CA, 'Merla', 'Ocell'),
                new BirdTranslationRequest(LanguageCode::ES, 'Mirlo', 'Pájaro'),
            ],
            image: $image,
            audio: $audio,
        );
    }

    private function createBirdWithTranslations(): Bird
    {
        $bird = Bird::create(
            new BirdId(self::BIRD_UUID),
            new ScientificName('Turdus merula'),
            new ImagePath('/img/test.webp'),
            new AudioPath('/audio/test.ogg'),
            new InformationUrl('https://example.com/bird'),
        );
        foreach (LanguageCode::cases() as $i => $lang) {
            $bird->addTranslation(new BirdTranslation(
                new BirdTranslationId('550e8400-e29b-41d4-a716-44665544001' . $i),
                $bird, $lang, new CommonName('Merla'), new BirdDescription('Ocell.'),
            ));
        }
        return $bird;
    }
}
