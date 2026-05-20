<?php

declare(strict_types=1);

namespace App\Tests\BirdCatalog\Application\Service;

use App\BirdCatalog\Application\Service\BirdCatalogUpdaterService;
use App\BirdCatalog\Domain\Entity\Bird;
use App\BirdCatalog\Domain\Entity\BirdCatalogVersion;
use App\BirdCatalog\Domain\Repository\BirdCatalogVersionRepositoryInterface;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\BirdCatalog\Domain\ValueObject\InformationUrl;
use App\BirdCatalog\Domain\ValueObject\ScientificName;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\ValueObject\AudioPath;
use App\Shared\Domain\ValueObject\ImagePath;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class BirdCatalogUpdaterServiceTest extends TestCase
{
    private const string BIRD_UUID = '550e8400-e29b-41d4-a716-446655440001';

    private BirdRepositoryInterface&MockObject             $birdRepository;
    private BirdCatalogVersionRepositoryInterface&MockObject $versionRepository;
    private TransactionManagerInterface&MockObject         $transactionManager;
    private BirdCatalogUpdaterService                      $updater;

    protected function setUp(): void
    {
        $this->birdRepository     = $this->createMock(BirdRepositoryInterface::class);
        $this->versionRepository  = $this->createMock(BirdCatalogVersionRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->updater            = new BirdCatalogUpdaterService(
            $this->birdRepository,
            $this->versionRepository,
            $this->transactionManager,
        );

        $this->versionRepository->method('get')->willReturn(BirdCatalogVersion::initialize());
    }

    public function test_save_saves_bird_increments_version_and_commits(): void
    {
        $bird = $this->createBird();

        $this->transactionManager->expects(self::once())->method('begin');
        $this->birdRepository->expects(self::once())->method('save')->with($bird);
        $this->versionRepository->expects(self::once())->method('save');
        $this->transactionManager->expects(self::once())->method('commit');

        $this->updater->save($bird);
    }

    public function test_save_rolls_back_on_exception(): void
    {
        $bird = $this->createBird();

        $this->birdRepository->method('save')->willThrowException(new \RuntimeException('DB error'));
        $this->transactionManager->expects(self::once())->method('rollback');

        $this->expectException(\RuntimeException::class);

        $this->updater->save($bird);
    }

    public function test_delete_deletes_bird_increments_version_and_commits(): void
    {
        $bird = $this->createBird();

        $this->transactionManager->expects(self::once())->method('begin');
        $this->birdRepository->expects(self::once())->method('delete')->with($bird);
        $this->versionRepository->expects(self::once())->method('save');
        $this->transactionManager->expects(self::once())->method('commit');

        $this->updater->delete($bird);
    }

    public function test_delete_rolls_back_on_exception(): void
    {
        $bird = $this->createBird();

        $this->birdRepository->method('delete')->willThrowException(new \RuntimeException('DB error'));
        $this->transactionManager->expects(self::once())->method('rollback');

        $this->expectException(\RuntimeException::class);

        $this->updater->delete($bird);
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
