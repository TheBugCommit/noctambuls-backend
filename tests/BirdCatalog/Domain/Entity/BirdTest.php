<?php

declare(strict_types=1);

namespace App\Tests\BirdCatalog\Domain\Entity;

use App\BirdCatalog\Domain\Entity\Bird;
use App\BirdCatalog\Domain\Entity\BirdTranslation;
use App\BirdCatalog\Domain\Exception\AlreadyExistsTranslationException;
use App\BirdCatalog\Domain\Exception\BirdMustHaveAllTranslations;
use App\BirdCatalog\Domain\Exception\TranslationNotFoundException;
use App\BirdCatalog\Domain\ValueObject\BirdDescription;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\BirdCatalog\Domain\ValueObject\BirdTranslationId;
use App\BirdCatalog\Domain\ValueObject\CommonName;
use App\BirdCatalog\Domain\ValueObject\InformationUrl;
use App\BirdCatalog\Domain\ValueObject\LanguageCode;
use App\BirdCatalog\Domain\ValueObject\ScientificName;
use App\Shared\Domain\ValueObject\AudioPath;
use App\Shared\Domain\ValueObject\ImagePath;
use PHPUnit\Framework\TestCase;

final class BirdTest extends TestCase
{
    private const string BIRD_UUID  = '550e8400-e29b-41d4-a716-446655440001';
    private const string TRANS_UUID = '550e8400-e29b-41d4-a716-446655440010';

    public function test_create_returns_bird_with_correct_properties(): void
    {
        $bird = $this->createBird();

        self::assertSame(self::BIRD_UUID, $bird->id()->value());
        self::assertSame('Turdus merula', $bird->scientificName()->value());
        self::assertSame('/img/test.webp', $bird->imagePath()->value());
        self::assertSame('/audio/test.ogg', $bird->audioPath()->value());
        self::assertSame('https://example.com/bird', $bird->informationUrl()->value());
        self::assertCount(0, $bird->translations());
    }

    public function test_update_changes_all_mutable_fields(): void
    {
        $bird = $this->createBird();

        $bird->update(
            new ScientificName('Erithacus rubecula'),
            new ImagePath('/img/new.webp'),
            new AudioPath('/audio/new.ogg'),
            new InformationUrl('https://example.com/new'),
        );

        self::assertSame('Erithacus rubecula', $bird->scientificName()->value());
        self::assertSame('/img/new.webp', $bird->imagePath()->value());
        self::assertSame('/audio/new.ogg', $bird->audioPath()->value());
        self::assertSame('https://example.com/new', $bird->informationUrl()->value());
    }

    public function test_add_translation_adds_to_collection(): void
    {
        $bird = $this->createBird();

        $bird->addTranslation($this->createTranslation($bird, self::TRANS_UUID, LanguageCode::CA));

        self::assertCount(1, $bird->translations());
    }

    public function test_add_translation_throws_when_duplicate_language(): void
    {
        $bird = $this->createBird();
        $bird->addTranslation($this->createTranslation($bird, self::TRANS_UUID, LanguageCode::CA));

        $this->expectException(AlreadyExistsTranslationException::class);

        $bird->addTranslation($this->createTranslation($bird, '550e8400-e29b-41d4-a716-446655440011', LanguageCode::CA));
    }

    public function test_update_translation_updates_existing(): void
    {
        $bird = $this->createBird();
        $bird->addTranslation($this->createTranslation($bird, self::TRANS_UUID, LanguageCode::CA));

        $bird->updateTranslation(LanguageCode::CA, new CommonName('Nou nom'), new BirdDescription('Nova desc'));

        $updated = $bird->translations()->first();
        self::assertSame('Nou nom', $updated->commonName()->value());
        self::assertSame('Nova desc', $updated->description()->value());
    }

    public function test_update_translation_throws_when_language_not_found(): void
    {
        $bird = $this->createBird();

        $this->expectException(TranslationNotFoundException::class);

        $bird->updateTranslation(LanguageCode::CA, new CommonName('X'), new BirdDescription('X'));
    }

    public function test_assert_has_required_translations_passes_when_all_present(): void
    {
        $bird = $this->createBird();
        $this->addAllTranslations($bird);

        $bird->assertHasRequiredTranslations();

        $this->addToAssertionCount(1);
    }

    public function test_assert_has_required_translations_throws_when_missing(): void
    {
        $bird = $this->createBird();
        $bird->addTranslation($this->createTranslation($bird, self::TRANS_UUID, LanguageCode::CA));

        $this->expectException(BirdMustHaveAllTranslations::class);

        $bird->assertHasRequiredTranslations();
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

    private function createTranslation(Bird $bird, string $uuid, LanguageCode $lang): BirdTranslation
    {
        return new BirdTranslation(
            new BirdTranslationId($uuid),
            $bird,
            $lang,
            new CommonName('Merla'),
            new BirdDescription('Ocell de jardí.'),
        );
    }

    private function addAllTranslations(Bird $bird): void
    {
        foreach (LanguageCode::cases() as $i => $lang) {
            $bird->addTranslation($this->createTranslation(
                $bird,
                '550e8400-e29b-41d4-a716-44665544001' . $i,
                $lang,
            ));
        }
    }
}
