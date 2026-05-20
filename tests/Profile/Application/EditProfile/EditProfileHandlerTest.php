<?php

declare(strict_types=1);

namespace App\Tests\Profile\Application\EditProfile;

use App\Profile\Application\EditProfile\EditProfileHandler;
use App\Profile\Application\EditProfile\EditProfileRequest;
use App\Profile\Application\Mapper\ProfileResponseMapper;
use App\Profile\Domain\Entity\Profile;
use App\Profile\Domain\Exception\ProfileNotFoundException;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\OrnithologistCode;
use App\Profile\Domain\ValueObject\PhoneNumber;
use App\Profile\Domain\ValueObject\ProfileId;
use App\Profile\Domain\ValueObject\ProfileName;
use App\Shared\Domain\Service\TransactionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EditProfileHandlerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private ProfileRepositoryInterface&MockObject $repository;
    private TransactionManagerInterface&MockObject $transactionManager;
    private EditProfileHandler $handler;

    protected function setUp(): void
    {
        $this->repository         = $this->createMock(ProfileRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->handler            = new EditProfileHandler(
            $this->repository,
            $this->transactionManager,
            new ProfileResponseMapper()
        );
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

    public function test_throws_profile_not_found_when_repository_returns_null(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->expectException(ProfileNotFoundException::class);

        $this->handler->handle(new EditProfileRequest(self::UUID, 'New Name', null));
    }

    public function test_updates_name_when_request_name_provided(): void
    {
        $profile = $this->makeProfile();
        $this->repository->method('findById')->willReturn($profile);

        $this->handler->handle(new EditProfileRequest(self::UUID, 'New Name', null));

        self::assertSame('New Name', $profile->name()->value());
    }

    public function test_updates_phone_when_request_phone_provided(): void
    {
        $profile = $this->makeProfile();
        $this->repository->method('findById')->willReturn($profile);

        $this->handler->handle(new EditProfileRequest(self::UUID, null, '699000000'));

        self::assertSame('699000000', $profile->phone()->value());
    }

    public function test_keeps_existing_name_when_request_name_is_null(): void
    {
        $profile = $this->makeProfile();
        $this->repository->method('findById')->willReturn($profile);

        $this->handler->handle(new EditProfileRequest(self::UUID, null, null));

        self::assertSame('Joan Doe', $profile->name()->value());
    }

    public function test_keeps_existing_phone_when_request_phone_is_null(): void
    {
        $profile = $this->makeProfile();
        $this->repository->method('findById')->willReturn($profile);

        $this->handler->handle(new EditProfileRequest(self::UUID, null, null));

        self::assertSame('611000000', $profile->phone()->value());
    }

    public function test_transaction_order_is_begin_update_save_commit(): void
    {
        $profile = $this->makeProfile();
        $this->repository->method('findById')->willReturn($profile);

        $order = [];
        $this->transactionManager->method('begin')->willReturnCallback(function () use (&$order) { $order[] = 'begin'; });
        $this->repository->method('save')->willReturnCallback(function () use (&$order) { $order[] = 'save'; });
        $this->transactionManager->method('commit')->willReturnCallback(function () use (&$order) { $order[] = 'commit'; });

        $this->handler->handle(new EditProfileRequest(self::UUID, 'New Name', null));

        self::assertSame(['begin', 'save', 'commit'], $order);
    }

    public function test_rolls_back_and_rethrows_on_save_failure(): void
    {
        $profile = $this->makeProfile();
        $this->repository->method('findById')->willReturn($profile);

        $exception = new \RuntimeException('DB error');
        $this->repository->method('save')->willThrowException($exception);

        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB error');

        $this->handler->handle(new EditProfileRequest(self::UUID, 'New Name', null));
    }

    public function test_returns_edit_profile_response(): void
    {
        $profile = $this->makeProfile();
        $this->repository->method('findById')->willReturn($profile);

        $response = $this->handler->handle(new EditProfileRequest(self::UUID, 'New Name', '699000000'));

        self::assertSame(self::UUID, $response->id);
        self::assertSame('New Name', $response->name);
        self::assertSame('699000000', $response->phone);
    }
}
