<?php

declare(strict_types=1);

namespace App\Tests\Profile\Application\GetProfile;

use App\Profile\Application\GetProfile\GetProfileHandler;
use App\Profile\Application\GetProfile\GetProfileRequest;
use App\Profile\Application\Mapper\ProfileResponseMapper;
use App\Profile\Domain\Entity\Profile;
use App\Profile\Domain\Exception\ProfileNotFoundException;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\OrnithologistCode;
use App\Profile\Domain\ValueObject\PhoneNumber;
use App\Profile\Domain\ValueObject\ProfileId;
use App\Profile\Domain\ValueObject\ProfileName;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetProfileHandlerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private ProfileRepositoryInterface&MockObject $repository;
    private GetProfileHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProfileRepositoryInterface::class);
        $this->handler    = new GetProfileHandler($this->repository, new ProfileResponseMapper());
    }

    private function makeProfile(): Profile
    {
        return Profile::create(
            new ProfileId(self::UUID),
            new ProfileName('Joan Doe'),
            new PhoneNumber('611000000'),
            new OrnithologistCode('#OR-JD-0001'),
        );
    }

    public function test_returns_get_profile_response_when_profile_found(): void
    {
        $this->repository->method('findById')->willReturn($this->makeProfile());

        $response = $this->handler->handle(new GetProfileRequest(self::UUID));

        self::assertNotNull($response);
    }

    public function test_response_contains_correct_field_values(): void
    {
        $this->repository->method('findById')->willReturn($this->makeProfile());

        $response = $this->handler->handle(new GetProfileRequest(self::UUID));

        self::assertSame(self::UUID, $response->id);
        self::assertSame('Joan Doe', $response->name);
        self::assertSame('611000000', $response->phone);
        self::assertSame('#OR-JD-0001', $response->ornithologistCode);
    }

    public function test_throws_profile_not_found_when_repository_returns_null(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->expectException(ProfileNotFoundException::class);

        $this->handler->handle(new GetProfileRequest(self::UUID));
    }

    public function test_passes_correct_profile_id_to_repository(): void
    {
        $this->repository->expects(self::once())
            ->method('findById')
            ->with(self::callback(fn (ProfileId $id) => $id->value() === self::UUID))
            ->willReturn($this->makeProfile());

        $this->handler->handle(new GetProfileRequest(self::UUID));
    }
}
