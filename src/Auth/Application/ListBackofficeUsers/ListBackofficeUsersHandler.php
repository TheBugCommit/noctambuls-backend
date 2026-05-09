<?php

declare(strict_types=1);

namespace App\Auth\Application\ListBackofficeUsers;

use App\Auth\Application\Mapper\AuthResponseMapper;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\Role;

final readonly class ListBackofficeUsersHandler
{
    public function __construct(private AuthUserRepositoryInterface $userRepository)
    {
    }

    public function handle(): ListBackofficeUsersResponse
    {
        $users = $this->userRepository->findByRoles([Role::ADMIN, Role::REVIEWER]);

        return AuthResponseMapper::toListBackofficeUsersResponse($users);
    }
}
