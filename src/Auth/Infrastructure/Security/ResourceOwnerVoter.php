<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Domain\ValueObject\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ResourceOwnerVoter extends Voter
{
    public const string VIEW = 'OWNER_ACCESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && is_string($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof JwtAuthenticatedUserInterface) {
            return false;
        }

        if (in_array(Role::ADMIN->value, $user->getRoles(), strict: true)) {
            return true;
        }

        return $user->getAuthUserId() === $subject;
    }
}
