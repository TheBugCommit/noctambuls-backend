<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\AnonymizeUser;

use App\Auth\Application\AnonymizeUser\AnonymizeUserHandler;
use App\Auth\Application\AnonymizeUser\AnonymizeUserRequest;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Exception\UserAlreadyAnonymizedException;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Profile\Domain\Entity\Profile;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AnonymizeUserHandlerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private AuthUserRepositoryInterface&MockObject $authUserRepository;
    private ProfileRepositoryInterface&MockObject  $profileRepository;
    private TransactionManagerInterface&MockObject $transactionManager;
    private AnonymizeUserHandler                   $handler;

    protected function setUp(): void
    {
        $this->authUserRepository = $this->createMock(AuthUserRepositoryInterface::class);
        $this->profileRepository  = $this->createMock(ProfileRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->handler = new AnonymizeUserHandler(
            $this->authUserRepository,
            $this->profileRepository,
            $this->transactionManager,
        );
    }

    public function test_handle_anonymizes_user_successfully(): void
    {
        $user    = AuthUser::create(new AuthUserId(self::UUID), new Email('user@example.com'), Provider::google());
        $profile = $this->createMock(Profile::class);

        $this->authUserRepository->method('findById')->willReturn($user);
        $this->profileRepository->method('findById')->willReturn($profile);
        $profile->expects(self::once())->method('anonymize');
        $this->authUserRepository
            ->expects(self::once())
            ->method('deleteRefreshTokensByEmail')
            ->with(self::callback(fn(Email $e) => $e->value() === 'user@example.com'));
        $this->transactionManager->expects(self::once())->method('begin');
        $this->transactionManager->expects(self::once())->method('commit');
        $this->transactionManager->expects(self::never())->method('rollback');

        $response = $this->handler->handle(new AnonymizeUserRequest(self::UUID));

        self::assertSame(self::UUID, $response->userId);
        self::assertSame(self::UUID . '@deleted.noctambuls.local', $user->email()->value());
        self::assertTrue($user->isAnonymized());
    }

    public function test_handle_works_when_profile_is_null(): void
    {
        $user = AuthUser::create(new AuthUserId(self::UUID), new Email('user@example.com'), Provider::google());

        $this->authUserRepository->method('findById')->willReturn($user);
        $this->profileRepository->method('findById')->willReturn(null);
        $this->transactionManager->expects(self::once())->method('commit');
        $this->transactionManager->expects(self::never())->method('rollback');

        $response = $this->handler->handle(new AnonymizeUserRequest(self::UUID));

        self::assertSame(self::UUID, $response->userId);
    }

    public function test_handle_throws_when_user_not_found(): void
    {
        $this->authUserRepository->method('findById')->willReturn(null);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(UserNotFoundException::class);

        $this->handler->handle(new AnonymizeUserRequest(self::UUID));
    }

    public function test_handle_throws_when_user_already_anonymized(): void
    {
        $user = AuthUser::create(new AuthUserId(self::UUID), new Email('user@example.com'), Provider::google());
        $user->anonymize(new Email(self::UUID . '@deleted.noctambuls.local'));

        $this->authUserRepository->method('findById')->willReturn($user);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(UserAlreadyAnonymizedException::class);

        $this->handler->handle(new AnonymizeUserRequest(self::UUID));
    }

    public function test_handle_rolls_back_on_exception(): void
    {
        $user = AuthUser::create(new AuthUserId(self::UUID), new Email('user@example.com'), Provider::google());

        $this->authUserRepository->method('findById')->willReturn($user);
        $this->profileRepository->method('findById')->willReturn(null);
        $this->authUserRepository->method('deleteRefreshTokensByEmail')->willThrowException(new \RuntimeException('DB error'));
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle(new AnonymizeUserRequest(self::UUID));
    }
}
