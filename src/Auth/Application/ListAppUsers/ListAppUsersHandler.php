<?php

declare(strict_types=1);

namespace App\Auth\Application\ListAppUsers;

use App\Auth\Application\Mapper\AuthResponseMapper;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\Role;
use App\Profile\Domain\Entity\Profile;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\ProfileId;

final readonly class ListAppUsersHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $authUserRepository,
        private ProfileRepositoryInterface  $profileRepository,
    )
    {
    }

    public function handle(ListAppUsersRequest $request): ListAppUsersResponse
    {
        $users = $this->authUserRepository->findPaginatedByRole(Role::USER, $request->page, $request->limit);
        $total = $this->authUserRepository->countByRole(Role::USER);

        $profileIds = array_map(
            fn(AuthUser $u) => new ProfileId($u->id()->value()),
            $users
        );
        $profiles = $this->profileRepository->findByIds($profileIds);

        return AuthResponseMapper::toListAppUsersResponse($users, $profiles, $total, $request->page, $request->limit);
    }
}
