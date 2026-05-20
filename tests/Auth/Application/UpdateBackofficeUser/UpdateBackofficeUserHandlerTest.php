<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\UpdateBackofficeUser;

use App\Auth\Application\UpdateBackofficeUser\UpdateBackofficeUserHandler;
use App\Auth\Application\UpdateBackofficeUser\UpdateBackofficeUserRequest;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Exception\UserAlreadyExistsException;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\PasswordHasherInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\Role;
use App\Shared\Domain\Service\TransactionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateBackofficeUserHandlerTest extends TestCase
{
    private const string UUID   = '550e8400-e29b-41d4-a716-446655440000';
    private const string UUID_2 = '550e8400-e29b-41d4-a716-446655440001';

    private AuthUserRepositoryInterface&MockObject $userRepository;
    private PasswordHasherInterface&MockObject     $passwordHasher;
    private TransactionManagerInterface&MockObject $transactionManager;
    private UpdateBackofficeUserHandler            $handler;

    protected function setUp(): void
    {
        $this->userRepository     = $this->createMock(AuthUserRepositoryInterface::class);
        $this->passwordHasher     = $this->createMock(PasswordHasherInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->handler = new UpdateBackofficeUserHandler(
            $this->userRepository,
            $this->passwordHasher,
            $this->transactionManager,
        );
    }

    public function test_handle_updates_email_successfully(): void
    {
        $user = AuthUser::create(new AuthUserId(self::UUID), new Email('old@example.com'), Provider::internal(), Role::ADMIN);

        $this->userRepository->method('findById')->willReturn($user);
        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->transactionManager->expects(self::once())->method('commit');
        $this->transactionManager->expects(self::never())->method('rollback');

        $response = $this->handler->handle(new UpdateBackofficeUserRequest(self::UUID, email: 'new@example.com'));

        self::assertSame(self::UUID, $response->userId);
        self::assertSame('new@example.com', $user->email()->value());
    }

    public function test_handle_updates_password_successfully(): void
    {
        $user       = AuthUser::create(new AuthUserId(self::UUID), new Email('admin@example.com'), Provider::internal(), Role::ADMIN);
        $bcryptHash = password_hash('NewValidPass1!', PASSWORD_BCRYPT);

        $this->userRepository->method('findById')->willReturn($user);
        $this->passwordHasher->expects(self::once())->method('hash')->with('NewValidPass1!')->willReturn($bcryptHash);
        $this->transactionManager->expects(self::once())->method('commit');
        $this->transactionManager->expects(self::never())->method('rollback');

        $response = $this->handler->handle(new UpdateBackofficeUserRequest(self::UUID, plainPassword: 'NewValidPass1!'));

        self::assertSame(self::UUID, $response->userId);
        self::assertSame($bcryptHash, $user->hashedPassword()?->value());
    }

    public function test_handle_updates_role_successfully(): void
    {
        $user = AuthUser::create(new AuthUserId(self::UUID), new Email('admin@example.com'), Provider::internal(), Role::ADMIN);

        $this->userRepository->method('findById')->willReturn($user);
        $this->transactionManager->expects(self::once())->method('commit');
        $this->transactionManager->expects(self::never())->method('rollback');

        $response = $this->handler->handle(new UpdateBackofficeUserRequest(self::UUID, role: 'ROLE_REVIEWER'));

        self::assertSame(self::UUID, $response->userId);
        self::assertSame(Role::REVIEWER, $user->role());
    }

    public function test_handle_throws_when_user_not_found(): void
    {
        $this->userRepository->method('findById')->willReturn(null);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(UserNotFoundException::class);

        $this->handler->handle(new UpdateBackofficeUserRequest(self::UUID, email: 'new@example.com'));
    }

    public function test_handle_throws_when_new_email_already_taken_by_another_user(): void
    {
        $user       = AuthUser::create(new AuthUserId(self::UUID), new Email('admin@example.com'), Provider::internal(), Role::ADMIN);
        $otherUser  = AuthUser::create(new AuthUserId(self::UUID_2), new Email('taken@example.com'), Provider::internal(), Role::REVIEWER);

        $this->userRepository->method('findById')->willReturn($user);
        $this->userRepository->method('findByEmail')->willReturn($otherUser);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(UserAlreadyExistsException::class);

        $this->handler->handle(new UpdateBackofficeUserRequest(self::UUID, email: 'taken@example.com'));
    }

    public function test_handle_allows_same_user_keep_their_email(): void
    {
        $user = AuthUser::create(new AuthUserId(self::UUID), new Email('admin@example.com'), Provider::internal(), Role::ADMIN);

        $this->userRepository->method('findById')->willReturn($user);
        $this->userRepository->method('findByEmail')->willReturn($user);
        $this->transactionManager->expects(self::once())->method('commit');
        $this->transactionManager->expects(self::never())->method('rollback');

        $response = $this->handler->handle(new UpdateBackofficeUserRequest(self::UUID, email: 'admin@example.com'));

        self::assertSame(self::UUID, $response->userId);
    }

    public function test_handle_throws_on_invalid_role(): void
    {
        $user = AuthUser::create(new AuthUserId(self::UUID), new Email('admin@example.com'), Provider::internal(), Role::ADMIN);

        $this->userRepository->method('findById')->willReturn($user);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\InvalidArgumentException::class);

        $this->handler->handle(new UpdateBackofficeUserRequest(self::UUID, role: 'ROLE_USER'));
    }

    public function test_handle_throws_on_password_too_short(): void
    {
        $user = AuthUser::create(new AuthUserId(self::UUID), new Email('admin@example.com'), Provider::internal(), Role::ADMIN);

        $this->userRepository->method('findById')->willReturn($user);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must be at least 8 characters.');

        $this->handler->handle(new UpdateBackofficeUserRequest(self::UUID, plainPassword: '1234567'));
    }

    public function test_handle_rolls_back_on_exception(): void
    {
        $user = AuthUser::create(new AuthUserId(self::UUID), new Email('admin@example.com'), Provider::internal(), Role::ADMIN);

        $this->userRepository->method('findById')->willReturn($user);
        $this->passwordHasher->method('hash')->willThrowException(new \RuntimeException('Hashing failed'));
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle(new UpdateBackofficeUserRequest(self::UUID, plainPassword: 'ValidPass1!'));
    }
}
