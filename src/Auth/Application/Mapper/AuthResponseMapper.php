<?php

declare(strict_types=1);

namespace App\Auth\Application\Mapper;

use App\Auth\Application\AnonymizeUser\AnonymizeUserResponse;
use App\Auth\Application\BlockUser\BlockUserResponse;
use App\Auth\Application\ChangeUserRole\ChangeUserRoleResponse;
use App\Auth\Application\CreateBackofficeUser\CreateBackofficeUserResponse;
use App\Auth\Application\DeleteBackofficeUser\DeleteBackofficeUserResponse;
use App\Auth\Application\ListAppUsers\ListAppUsersResponse;
use App\Auth\Application\ListBackofficeUsers\ListBackofficeUsersResponse;
use App\Auth\Application\SetUserPassword\SetUserPasswordResponse;
use App\Auth\Application\SignIn\SignInResponse;
use App\Auth\Application\SignUp\SignUpResponse;
use App\Auth\Application\UnblockUser\UnblockUserResponse;
use App\Auth\Application\UpdateBackofficeUser\UpdateBackofficeUserResponse;
use App\Auth\Domain\Entity\AuthUser;

final class AuthResponseMapper
{
    public static function toSignInResponse(string $accessToken, string $refreshToken): SignInResponse
    {
        return new SignInResponse($accessToken, $refreshToken);
    }

    public static function toSignUpResponse(string $accessToken, string $refreshToken): SignUpResponse
    {
        return new SignUpResponse($accessToken, $refreshToken);
    }

    public static function toChangeUserRoleResponse(string $userId, string $role): ChangeUserRoleResponse
    {
        return new ChangeUserRoleResponse($userId, $role);
    }

    public static function toSetUserPasswordResponse(string $userId): SetUserPasswordResponse
    {
        return new SetUserPasswordResponse($userId);
    }

    public static function toAnonymizeUserResponse(string $userId): AnonymizeUserResponse
    {
        return new AnonymizeUserResponse($userId);
    }

    public static function toBlockUserResponse(string $userId): BlockUserResponse
    {
        return new BlockUserResponse($userId);
    }

    public static function toUnblockUserResponse(string $userId): UnblockUserResponse
    {
        return new UnblockUserResponse($userId);
    }

    public static function toCreateBackofficeUserResponse(string $userId): CreateBackofficeUserResponse
    {
        return new CreateBackofficeUserResponse($userId);
    }

    public static function toDeleteBackofficeUserResponse(string $userId): DeleteBackofficeUserResponse
    {
        return new DeleteBackofficeUserResponse($userId);
    }

    public static function toUpdateBackofficeUserResponse(string $userId): UpdateBackofficeUserResponse
    {
        return new UpdateBackofficeUserResponse($userId);
    }

    public static function toListAppUsersResponse(array $users, array $profiles, int $total, int $page, int $limit): ListAppUsersResponse
    {
        $profileMap = [];
        foreach ($profiles as $profile) {
            $profileMap[$profile->id()->value()] = $profile;
        }

        $data = array_map(function (AuthUser $user) use ($profileMap): array {
            $profile = $profileMap[$user->id()->value()] ?? null;

            return [
                'id' => $user->id()->value(),
                'email' => $user->email()->value(),
                'name' => $profile?->name()->value(),
                'phone' => $profile?->phone()->value(),
                'ornithologistCode' => $profile?->ornithologistCode()->value(),
                'createdAt' => $user->createdAt()->format('c'),
                'updatedAt' => $user->updatedAt()->format('c'),
                'isBlocked' => $user->isBlocked(),
                'isAnonymized' => $user->isAnonymized(),
            ];
        }, $users);

        return new ListAppUsersResponse($data, $total, $page, $limit);
    }

    public static function toListBackofficeUsersResponse(array $authUsersEntities): ListBackofficeUsersResponse
    {
        $data = array_map(fn($u) => [
            'id' => $u->id()->value(),
            'email' => $u->email()->value(),
            'role' => $u->role()->value,
            'createdAt' => $u->createdAt()->format('c'),
            'updatedAt' => $u->updatedAt()->format('c'),
        ], $authUsersEntities);

        return new ListBackofficeUsersResponse($data);
    }
}
