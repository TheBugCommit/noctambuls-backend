<?php

declare(strict_types=1);

namespace App\Tests\BirdCatalog\Application\Service;

use App\BirdCatalog\Application\BirdTranslation\BirdTranslationRequest;
use App\BirdCatalog\Application\Service\BirdService;
use App\BirdCatalog\Domain\Exception\BirdMustHaveAllTranslations;
use App\BirdCatalog\Domain\ValueObject\LanguageCode;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use App\Shared\Domain\ValueObject\AudioPath;
use App\Shared\Domain\ValueObject\ImagePath;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class BirdServiceTest extends TestCase
{
    private const string BIRD_UUID  = '550e8400-e29b-41d4-a716-446655440001';
    private const string TRANS_UUID = '550e8400-e29b-41d4-a716-446655440010';

    private UuidGeneratorInterface&MockObject $uuidGenerator;
    private BirdService                       $service;

    protected function setUp(): void
    {
        $this->uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
        $this->uuidGenerator->method('generate')->willReturnOnConsecutiveCalls(
            self::BIRD_UUID,
            self::TRANS_UUID,
            '550e8400-e29b-41d4-a716-446655440011',
        );
        $this->service = new BirdService($this->uuidGenerator);
    }

    public function test_create_returns_bird_with_correct_scientific_name(): void
    {
        $bird = $this->service->create(
            'Turdus merula',
            new ImagePath('/img/test.webp'),
            new AudioPath('/audio/test.ogg'),
            'https://example.com',
            $this->allTranslations(),
        );

        self::assertSame('Turdus merula', $bird->scientificName()->value());
    }

    public function test_create_adds_translations_to_bird(): void
    {
        $bird = $this->service->create(
            'Turdus merula',
            new ImagePath('/img/test.webp'),
            new AudioPath('/audio/test.ogg'),
            'https://example.com',
            $this->allTranslations(),
        );

        self::assertCount(2, $bird->translations());
    }

    public function test_create_throws_when_translations_incomplete(): void
    {
        $this->expectException(BirdMustHaveAllTranslations::class);

        $this->service->create(
            'Turdus merula',
            new ImagePath('/img/test.webp'),
            new AudioPath('/audio/test.ogg'),
            'https://example.com',
            [new BirdTranslationRequest(LanguageCode::CA, 'Merla', 'Ocell')],
        );
    }

    /** @return BirdTranslationRequest[] */
    private function allTranslations(): array
    {
        return [
            new BirdTranslationRequest(LanguageCode::CA, 'Merla', 'Ocell de jardí.'),
            new BirdTranslationRequest(LanguageCode::ES, 'Mirlo', 'Pájaro de jardín.'),
        ];
    }
}
