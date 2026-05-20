<?php

declare(strict_types=1);

namespace App\Tests\Auth\Application\ListBackofficeUsers;

use App\Auth\Application\ListBackofficeUsers\ListBackofficeUsersHandler;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\Role;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ListBackofficeUsersHandlerTest extends TestCase
{
    private const string UUID_1 = '550e8400-e29b-41d4-a716-446655440001';
    private const string UUID_2 = '550e8400-e29b-41d4-a716-446655440002';

    private AuthUserRepositoryInterface&MockObject $userRepository;
    private ListBackofficeUsersHandler             $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(AuthUserRepositoryInterface::class);
        $this->handler        = new ListBackofficeUsersHandler($this->userRepository);
    }

    public function test_handle_returns_backoffice_users(): void
    {
        $users = [
            AuthUser::create(new AuthUserId(self::UUID_1), new Email('admin@example.com'), Provider::internal(), Role::ADMIN),
            AuthUser::create(new AuthUserId(self::UUID_2), new Email('reviewer@example.com'), Provider::internal(), Role::REVIEWER),
        ];

        $this->userRepository->method('findByRoles')->willReturn($users);

        $response = $this->handler->handle();

        self::assertCount(2, $response->users);
        self::assertSame(self::UUID_1, $response->users[0]['id']);
        self::assertSame(self::UUID_2, $response->users[1]['id']);
    }

    public function test_handle_returns_empty_list(): void
    {
        $this->userRepository->method('findByRoles')->willReturn([]);

        $response = $this->handler->handle();

        self::assertSame([], $response->users);
    }

    public function test_handle_queries_correct_roles(): void
    {
        $this->userRepository
            ->expects(self::once())
            ->method('findByRoles')
            ->with(self::callback(function (array $roles): bool {
                return in_array(Role::ADMIN, $roles, true) && in_array(Role::REVIEWER, $roles, true);
            }))
            ->willReturn([]);

        $this->handler->handle();
    }
}
