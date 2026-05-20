<?php

declare(strict_types=1);

namespace App\Tests\Profile\Infrastructure\Service;

use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\ProfileName;
use App\Profile\Infrastructure\Service\UniqueOrnithologistCodeGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UniqueOrnithologistCodeGeneratorTest extends TestCase
{
    private ProfileRepositoryInterface&MockObject $repository;
    private UniqueOrnithologistCodeGenerator $generator;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProfileRepositoryInterface::class);
        $this->generator  = new UniqueOrnithologistCodeGenerator($this->repository);
    }

    public function test_returns_code_with_number_one_when_no_existing_codes(): void
    {
        $this->repository->method('findMaxNumberForPrefix')->willReturn(0);

        $code = $this->generator->generate(new ProfileName('Joan'));

        self::assertSame('#OR-J-0001', $code->value());
    }

    public function test_returns_code_with_next_sequential_number(): void
    {
        $this->repository->method('findMaxNumberForPrefix')->willReturn(5);

        $code = $this->generator->generate(new ProfileName('Joan'));

        self::assertSame('#OR-J-0006', $code->value());
    }

    public function test_passes_extracted_prefix_to_find_max(): void
    {
        $this->repository->expects(self::once())
            ->method('findMaxNumberForPrefix')
            ->with('#OR-JD-')
            ->willReturn(0);

        $this->generator->generate(new ProfileName('Joan Doe'));
    }

    public function test_throws_runtime_exception_when_codes_exhausted(): void
    {
        $this->repository->method('findMaxNumberForPrefix')->willReturn(9999);

        $this->expectException(\RuntimeException::class);

        $this->generator->generate(new ProfileName('Joan'));
    }
}
