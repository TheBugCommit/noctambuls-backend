<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\SetUserPassword;

use App\Auth\Application\SetUserPassword\SetUserPasswordHandler;
use App\Auth\Application\SetUserPassword\SetUserPasswordRequest;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\PasswordHasherInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Shared\Domain\Service\TransactionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SetUserPasswordHandlerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private AuthUserRepositoryInterface&MockObject $userRepository;
    private PasswordHasherInterface&MockObject     $passwordHasher;
    private TransactionManagerInterface&MockObject $transactionManager;
    private SetUserPasswordHandler                 $handler;

    protected function setUp(): void
    {
        $this->userRepository     = $this->createMock(AuthUserRepositoryInterface::class);
        $this->passwordHasher     = $this->createMock(PasswordHasherInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->handler = new SetUserPasswordHandler(
            $this->userRepository,
            $this->passwordHasher,
            $this->transactionManager,
        );
    }

    public function test_handle_sets_password_successfully(): void
    {
        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('user@example.com'),
            Provider::internal(),
        );

        $bcryptHash = password_hash('secret', PASSWORD_BCRYPT);

        $this->userRepository->method('findById')->willReturn($user);
        $this->passwordHasher->method('hash')->willReturn($bcryptHash);
        $this->transactionManager->expects(self::once())->method('begin');
        $this->transactionManager->expects(self::once())->method('commit');
        $this->transactionManager->expects(self::never())->method('rollback');

        $response = $this->handler->handle(new SetUserPasswordRequest(self::UUID, 'secret'));

        self::assertSame(self::UUID, $response->userId);
        self::assertNotNull($user->hashedPassword());
        self::assertSame($bcryptHash, $user->hashedPassword()->value());
    }

    public function test_handle_throws_when_user_not_found(): void
    {
        $this->userRepository->method('findById')->willReturn(null);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(UserNotFoundException::class);

        $this->handler->handle(new SetUserPasswordRequest(self::UUID, 'secret'));
    }

    public function test_handle_rolls_back_when_hasher_throws(): void
    {
        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('user@example.com'),
            Provider::internal(),
        );

        $this->userRepository->method('findById')->willReturn($user);
        $this->passwordHasher->method('hash')->willThrowException(new \RuntimeException('Hashing failed'));
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Hashing failed');

        $this->handler->handle(new SetUserPasswordRequest(self::UUID, 'secret'));
    }

    public function test_handle_calls_hasher_with_plain_password(): void
    {
        $user       = AuthUser::create(new AuthUserId(self::UUID), new Email('u@e.com'), Provider::internal());
        $bcryptHash = password_hash('my-plain-pass', PASSWORD_BCRYPT);

        $this->userRepository->method('findById')->willReturn($user);
        $this->passwordHasher
            ->expects(self::once())
            ->method('hash')
            ->with('my-plain-pass')
            ->willReturn($bcryptHash);

        $this->handler->handle(new SetUserPasswordRequest(self::UUID, 'my-plain-pass'));
    }

    public function test_handle_passes_correct_user_id_to_repository(): void
    {
        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with(self::callback(fn(AuthUserId $id) => $id->value() === self::UUID))
            ->willReturn(null);

        $this->transactionManager->method('rollback');

        try {
            $this->handler->handle(new SetUserPasswordRequest(self::UUID, 'secret'));
        } catch (UserNotFoundException) {
            // expected
        }
    }
}
