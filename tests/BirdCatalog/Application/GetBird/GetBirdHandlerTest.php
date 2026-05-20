<?php

declare(strict_types=1);

namespace App\Tests\BirdCatalog\Application\GetBird;

use App\BirdCatalog\Application\GetBird\GetBirdHandler;
use App\BirdCatalog\Application\GetBird\GetBirdRequest;
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
use App\Shared\Domain\ValueObject\AudioPath;
use App\Shared\Domain\ValueObject\ImagePath;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetBirdHandlerTest extends TestCase
{
    private const string BIRD_UUID = '550e8400-e29b-41d4-a716-446655440001';

    private BirdRepositoryInterface&MockObject $birdRepository;
    private GetBirdHandler                     $handler;

    protected function setUp(): void
    {
        $this->birdRepository = $this->createMock(BirdRepositoryInterface::class);
        $this->handler        = new GetBirdHandler($this->birdRepository);
    }

    public function test_handle_throws_when_bird_not_found(): void
    {
        $this->birdRepository->method('findById')->willReturn(null);

        $this->expectException(BirdNotFoundException::class);

        $this->handler->handle(new GetBirdRequest(self::BIRD_UUID));
    }

    public function test_handle_returns_response_with_correct_bird_id(): void
    {
        $bird = $this->createBirdWithTranslations();
        $this->birdRepository->method('findById')->willReturn($bird);

        $response = $this->handler->handle(new GetBirdRequest(self::BIRD_UUID));

        self::assertSame(self::BIRD_UUID, $response->id);
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
