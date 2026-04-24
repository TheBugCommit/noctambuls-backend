<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\ChangeUserRole;

use App\Auth\Application\ChangeUserRole\ChangeUserRoleHandler;
use App\Auth\Application\ChangeUserRole\ChangeUserRoleRequest;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\Role;
use App\Shared\Domain\Service\TransactionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ChangeUserRoleHandlerTest extends TestCase
{
    private const string UUID = '550e8400-e29b-41d4-a716-446655440000';

    private AuthUserRepositoryInterface&MockObject $userRepository;
    private TransactionManagerInterface&MockObject $transactionManager;
    private ChangeUserRoleHandler                  $handler;

    protected function setUp(): void
    {
        $this->userRepository     = $this->createMock(AuthUserRepositoryInterface::class);
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);

        $this->handler = new ChangeUserRoleHandler($this->userRepository, $this->transactionManager);
    }

    public function test_handle_changes_role_successfully(): void
    {
        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('user@example.com'),
            Provider::google()
        );

        $this->userRepository->method('findById')->willReturn($user);
        $this->transactionManager->expects(self::once())->method('begin');
        $this->transactionManager->expects(self::once())->method('commit');
        $this->transactionManager->expects(self::never())->method('rollback');

        $response = $this->handler->handle(new ChangeUserRoleRequest(self::UUID, 'ROLE_ADMIN'));

        self::assertSame(self::UUID, $response->userId);
        self::assertSame('ROLE_ADMIN', $response->role);
        self::assertSame(Role::ADMIN, $user->role());
    }

    public function test_handle_throws_when_user_not_found(): void
    {
        $this->userRepository->method('findById')->willReturn(null);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(UserNotFoundException::class);

        $this->handler->handle(new ChangeUserRoleRequest(self::UUID, 'ROLE_ADMIN'));
    }

    public function test_handle_throws_on_invalid_role(): void
    {
        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('user@example.com'),
            Provider::google(),
        );

        $this->userRepository->method('findById')->willReturn($user);
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid role: "ROLE_SUPERADMIN".');

        $this->handler->handle(new ChangeUserRoleRequest(self::UUID, 'ROLE_SUPERADMIN'));
    }

    public function test_handle_rolls_back_on_repository_exception(): void
    {
        $this->userRepository->method('findById')->willThrowException(new \RuntimeException('DB error'));
        $this->transactionManager->expects(self::once())->method('rollback');
        $this->transactionManager->expects(self::never())->method('commit');

        $this->expectException(\RuntimeException::class);

        $this->handler->handle(new ChangeUserRoleRequest(self::UUID, 'ROLE_REVIEWER'));
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
            $this->handler->handle(new ChangeUserRoleRequest(self::UUID, 'ROLE_ADMIN'));
        } catch (UserNotFoundException) {
            // expected
        }
    }

    public function test_handle_change_to_reviewer_role(): void
    {
        $user = AuthUser::create(
            new AuthUserId(self::UUID),
            new Email('user@example.com'),
            Provider::internal(),
            Role::USER,
        );

        $this->userRepository->method('findById')->willReturn($user);

        $response = $this->handler->handle(new ChangeUserRoleRequest(self::UUID, 'ROLE_REVIEWER'));

        self::assertSame('ROLE_REVIEWER', $response->role);
        self::assertSame(Role::REVIEWER, $user->role());
    }
}
