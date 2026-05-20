<?php

declare(strict_types=1);

namespace App\Tests\BirdCatalog\Domain\Entity;

use App\BirdCatalog\Domain\Entity\Bird;
use App\BirdCatalog\Domain\Entity\BirdTranslation;
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

final class BirdTranslationTest extends TestCase
{
    private const string TRANS_UUID_1 = '550e8400-e29b-41d4-a716-446655440010';
    private const string TRANS_UUID_2 = '550e8400-e29b-41d4-a716-446655440011';

    public function test_constructor_sets_all_properties(): void
    {
        $bird        = $this->createBird();
        $translation = new BirdTranslation(
            new BirdTranslationId(self::TRANS_UUID_1),
            $bird,
            LanguageCode::CA,
            new CommonName('Merla'),
            new BirdDescription('Ocell de jardí.'),
        );

        self::assertSame(self::TRANS_UUID_1, $translation->id()->value());
        self::assertSame($bird, $translation->bird());
        self::assertSame(LanguageCode::CA, $translation->languageCode());
        self::assertSame('Merla', $translation->commonName()->value());
        self::assertSame('Ocell de jardí.', $translation->description()->value());
    }

    public function test_update_changes_common_name_and_description(): void
    {
        $bird        = $this->createBird();
        $translation = new BirdTranslation(
            new BirdTranslationId(self::TRANS_UUID_1),
            $bird,
            LanguageCode::CA,
            new CommonName('Merla'),
            new BirdDescription('Ocell de jardí.'),
        );

        $translation->update(new CommonName('Nou nom'), new BirdDescription('Nova descripció'));

        self::assertSame('Nou nom', $translation->commonName()->value());
        self::assertSame('Nova descripció', $translation->description()->value());
    }

    public function test_equals_returns_true_for_same_id(): void
    {
        $bird = $this->createBird();
        $t1   = new BirdTranslation(new BirdTranslationId(self::TRANS_UUID_1), $bird, LanguageCode::CA, new CommonName('A'), new BirdDescription('B'));
        $t2   = new BirdTranslation(new BirdTranslationId(self::TRANS_UUID_1), $bird, LanguageCode::ES, new CommonName('C'), new BirdDescription('D'));

        self::assertTrue($t1->equals($t2));
    }

    public function test_equals_returns_false_for_different_id(): void
    {
        $bird = $this->createBird();
        $t1   = new BirdTranslation(new BirdTranslationId(self::TRANS_UUID_1), $bird, LanguageCode::CA, new CommonName('A'), new BirdDescription('B'));
        $t2   = new BirdTranslation(new BirdTranslationId(self::TRANS_UUID_2), $bird, LanguageCode::CA, new CommonName('A'), new BirdDescription('B'));

        self::assertFalse($t1->equals($t2));
    }

    private function createBird(): Bird
    {
        return Bird::create(
            new BirdId('550e8400-e29b-41d4-a716-446655440001'),
            new ScientificName('Turdus merula'),
            new ImagePath('/img/test.webp'),
            new AudioPath('/audio/test.ogg'),
            new InformationUrl('https://example.com/bird'),
        );
    }
}
