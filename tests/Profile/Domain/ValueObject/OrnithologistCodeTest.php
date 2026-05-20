<?php

declare(strict_types=1);

namespace App\Tests\Profile\Domain\ValueObject;

use App\Profile\Domain\ValueObject\OrnithologistCode;
use App\Profile\Domain\ValueObject\ProfileName;
use PHPUnit\Framework\TestCase;

final class OrnithologistCodeTest extends TestCase
{
    public function test_accepts_valid_code_with_one_initial(): void
    {
        $code = new OrnithologistCode('#OR-J-0001');

        self::assertSame('#OR-J-0001', $code->value());
    }

    public function test_accepts_valid_code_with_four_initials(): void
    {
        $code = new OrnithologistCode('#OR-ABCD-9999');

        self::assertSame('#OR-ABCD-9999', $code->value());
    }

    public function test_throws_on_missing_hash_prefix(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new OrnithologistCode('OR-J-0001');
    }

    public function test_throws_on_lowercase_initials(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new OrnithologistCode('#OR-j-0001');
    }

    public function test_throws_on_fewer_than_four_digits(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new OrnithologistCode('#OR-J-001');
    }

    public function test_throws_on_more_than_four_digits(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new OrnithologistCode('#OR-J-00001');
    }

    public function test_extract_prefix_from_single_word_name(): void
    {
        $prefix = OrnithologistCode::extractPrefixFromName(new ProfileName('Joan'));

        self::assertSame('#OR-J-', $prefix);
    }

    public function test_extract_prefix_from_two_word_name(): void
    {
        $prefix = OrnithologistCode::extractPrefixFromName(new ProfileName('Joan Doe'));

        self::assertSame('#OR-JD-', $prefix);
    }

    public function test_extract_prefix_takes_up_to_four_initials(): void
    {
        $prefix = OrnithologistCode::extractPrefixFromName(new ProfileName('Joan Doe Lee'));

        self::assertSame('#OR-JDL-', $prefix);
    }

    public function test_extract_prefix_stops_at_four_words(): void
    {
        $prefix = OrnithologistCode::extractPrefixFromName(new ProfileName('Joan Doe Lee Ann More'));

        self::assertSame('#OR-JDLA-', $prefix);
    }

    public function test_extract_prefix_falls_back_to_x_for_non_alpha_only_name(): void
    {
        $prefix = OrnithologistCode::extractPrefixFromName(new ProfileName('123'));

        self::assertSame('#OR-X-', $prefix);
    }

    public function test_generate_zero_pads_number_to_four_digits(): void
    {
        $code = OrnithologistCode::generate(new ProfileName('Joan'), 1);

        self::assertSame('#OR-J-0001', $code->value());
    }

    public function test_generate_with_max_number_9999(): void
    {
        $code = OrnithologistCode::generate(new ProfileName('Joan'), 9999);

        self::assertSame('#OR-J-9999', $code->value());
    }
}
