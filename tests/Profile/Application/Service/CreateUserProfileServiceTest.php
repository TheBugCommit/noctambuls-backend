<?php

declare(strict_types=1);

namespace App\Tests\Profile\Application\Service;

use App\Profile\Application\Service\CreateUserProfileService;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\Service\OrnithologistCodeGeneratorInterface;
use App\Profile\Domain\ValueObject\OrnithologistCode;
use App\Profile\Domain\ValueObject\ProfileName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateUserProfileServiceTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private ProfileRepositoryInterface&MockObject $repository;
    private OrnithologistCodeGeneratorInterface&MockObject $codeGenerator;
    private CreateUserProfileService $service;

    protected function setUp(): void
    {
        $this->repository    = $this->createMock(ProfileRepositoryInterface::class);
        $this->codeGenerator = $this->createMock(OrnithologistCodeGeneratorInterface::class);
        $this->service       = new CreateUserProfileService($this->repository, $this->codeGenerator);
    }

    public function test_passes_profile_name_to_code_generator(): void
    {
        $this->codeGenerator->expects(self::once())
            ->method('generate')
            ->with(self::callback(fn (ProfileName $name) => $name->value() === 'Joan Doe'))
            ->willReturn(new OrnithologistCode('#OR-JD-0001'));

        $this->repository->method('save');

        $this->service->execute(self::UUID, 'Joan Doe', '611000000');
    }

    public function test_saves_profile_via_repository(): void
    {
        $this->codeGenerator->method('generate')->willReturn(new OrnithologistCode('#OR-JD-0001'));

        $this->repository->expects(self::once())->method('save');

        $this->service->execute(self::UUID, 'Joan Doe', '611000000');
    }

    public function test_saved_profile_id_matches_provided_user_id(): void
    {
        $this->codeGenerator->method('generate')->willReturn(new OrnithologistCode('#OR-JD-0001'));

        $savedProfile = null;
        $this->repository->method('save')->willReturnCallback(function ($profile) use (&$savedProfile) {
            $savedProfile = $profile;
        });

        $this->service->execute(self::UUID, 'Joan Doe', '611000000');

        self::assertSame(self::UUID, $savedProfile->id()->value());
    }

    public function test_saved_profile_has_correct_phone(): void
    {
        $this->codeGenerator->method('generate')->willReturn(new OrnithologistCode('#OR-JD-0001'));

        $savedProfile = null;
        $this->repository->method('save')->willReturnCallback(function ($profile) use (&$savedProfile) {
            $savedProfile = $profile;
        });

        $this->service->execute(self::UUID, 'Joan Doe', '611000000');

        self::assertSame('611000000', $savedProfile->phone()->value());
    }
}
