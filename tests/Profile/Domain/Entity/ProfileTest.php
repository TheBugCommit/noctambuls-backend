<?php

declare(strict_types=1);

namespace App\Tests\Profile\Domain\Entity;

use App\Profile\Domain\Entity\Profile;
use App\Profile\Domain\ValueObject\OrnithologistCode;
use App\Profile\Domain\ValueObject\PhoneNumber;
use App\Profile\Domain\ValueObject\ProfileId;
use App\Profile\Domain\ValueObject\ProfileName;
use PHPUnit\Framework\TestCase;

final class ProfileTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private function makeProfile(): Profile
    {
        return Profile::create(
            new ProfileId(self::UUID),
            new ProfileName('Joan Doe'),
            new PhoneNumber('611000000'),
            new OrnithologistCode('#OR-JD-0001'),
        );
    }

    public function test_create_sets_all_fields(): void
    {
        $profile = $this->makeProfile();

        self::assertSame(self::UUID, $profile->id()->value());
        self::assertSame('Joan Doe', $profile->name()->value());
        self::assertSame('611000000', $profile->phone()->value());
        self::assertSame('#OR-JD-0001', $profile->ornithologistCode()->value());
    }

    public function test_create_sets_created_at_and_updated_at_to_utc(): void
    {
        $profile = $this->makeProfile();

        self::assertSame('UTC', $profile->createdAt()->getTimezone()->getName());
        self::assertSame('UTC', $profile->updatedAt()->getTimezone()->getName());
    }

    public function test_update_changes_name_and_phone(): void
    {
        $profile = $this->makeProfile();

        $profile->update(new ProfileName('New Name'), new PhoneNumber('699000000'));

        self::assertSame('New Name', $profile->name()->value());
        self::assertSame('699000000', $profile->phone()->value());
    }

    public function test_update_refreshes_updated_at(): void
    {
        $profile = $this->makeProfile();
        $originalUpdatedAt = $profile->updatedAt();

        usleep(1000);
        $profile->update(new ProfileName('New Name'), new PhoneNumber('699000000'));

        self::assertGreaterThanOrEqual($originalUpdatedAt, $profile->updatedAt());
    }

    public function test_update_does_not_change_id_code_or_created_at(): void
    {
        $profile = $this->makeProfile();
        $createdAt = $profile->createdAt();

        $profile->update(new ProfileName('New Name'), new PhoneNumber('699000000'));

        self::assertSame(self::UUID, $profile->id()->value());
        self::assertSame('#OR-JD-0001', $profile->ornithologistCode()->value());
        self::assertSame($createdAt, $profile->createdAt());
    }

    public function test_anonymize_sets_name_to_deleted_user(): void
    {
        $profile = $this->makeProfile();

        $profile->anonymize();

        self::assertSame('Deleted User', $profile->name()->value());
    }

    public function test_anonymize_sets_phone_to_placeholder_plus_zeros(): void
    {
        $profile = $this->makeProfile();

        $profile->anonymize();

        self::assertSame('+000000000', $profile->phone()->value());
    }

    public function test_anonymize_refreshes_updated_at(): void
    {
        $profile = $this->makeProfile();
        $originalUpdatedAt = $profile->updatedAt();

        usleep(1000);
        $profile->anonymize();

        self::assertGreaterThanOrEqual($originalUpdatedAt, $profile->updatedAt());
    }

    public function test_anonymize_does_not_change_id_or_code(): void
    {
        $profile = $this->makeProfile();

        $profile->anonymize();

        self::assertSame(self::UUID, $profile->id()->value());
        self::assertSame('#OR-JD-0001', $profile->ornithologistCode()->value());
    }
}
