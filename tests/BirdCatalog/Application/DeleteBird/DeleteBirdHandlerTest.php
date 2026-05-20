<?php

declare(strict_types=1);

namespace App\Tests\BirdCatalog\Application\DeleteBird;

use App\BirdCatalog\Application\DeleteBird\DeleteBirdHandler;
use App\BirdCatalog\Application\DeleteBird\DeleteBirdRequest;
use App\BirdCatalog\Application\Service\BirdCatalogUpdaterService;
use App\BirdCatalog\Domain\Entity\Bird;
use App\BirdCatalog\Domain\Exception\BirdNotFoundException;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\BirdCatalog\Domain\ValueObject\InformationUrl;
use App\BirdCatalog\Domain\ValueObject\ScientificName;
use App\Shared\Application\Service\FileRemoverInterface;
use App\Shared\Domain\ValueObject\AudioPath;
use App\Shared\Domain\ValueObject\ImagePath;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteBirdHandlerTest extends TestCase
{
    private const string BIRD_UUID = '550e8400-e29b-41d4-a716-446655440001';

    private BirdRepositoryInterface&MockObject    $birdRepository;
    private BirdCatalogUpdaterService&MockObject  $updaterService;
    private FileRemoverInterface&MockObject       $fileRemover;
    private DeleteBirdHandler                     $handler;

    protected function setUp(): void
    {
        $this->birdRepository = $this->createMock(BirdRepositoryInterface::class);
        $this->updaterService = $this->createMock(BirdCatalogUpdaterService::class);
        $this->fileRemover    = $this->createMock(FileRemoverInterface::class);
        $this->handler        = new DeleteBirdHandler(
            $this->birdRepository,
            $this->updaterService,
            $this->fileRemover,
        );
    }

    public function test_handle_throws_when_bird_not_found(): void
    {
        $this->birdRepository->method('findById')->willReturn(null);

        $this->expectException(BirdNotFoundException::class);

        $this->handler->handle(new DeleteBirdRequest(self::BIRD_UUID));
    }

    public function test_handle_deletes_bird_via_updater(): void
    {
        $bird = $this->createBird();
        $this->birdRepository->method('findById')->willReturn($bird);

        $this->updaterService->expects(self::once())->method('delete')->with($bird);

        $this->handler->handle(new DeleteBirdRequest(self::BIRD_UUID));
    }

    public function test_handle_removes_both_files_after_deletion(): void
    {
        $bird = $this->createBird();
        $this->birdRepository->method('findById')->willReturn($bird);

        $this->fileRemover->expects(self::exactly(2))->method('remove');

        $this->handler->handle(new DeleteBirdRequest(self::BIRD_UUID));
    }

    private function createBird(): Bird
    {
        return Bird::create(
            new BirdId(self::BIRD_UUID),
            new ScientificName('Turdus merula'),
            new ImagePath('/img/test.webp'),
            new AudioPath('/audio/test.ogg'),
            new InformationUrl('https://example.com/bird'),
        );
    }
}
