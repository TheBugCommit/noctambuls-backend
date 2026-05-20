<?php

declare(strict_types=1);

namespace App\Tests\Auth\Infrastructure\Security;

use App\Auth\Infrastructure\Security\JwtAuthenticatedUserInterface;
use App\Auth\Infrastructure\Security\ResourceOwnerVoter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class ResourceOwnerVoterTest extends TestCase
{
    private const string UUID_1 = '550e8400-e29b-41d4-a716-446655440001';
    private const string UUID_2 = '550e8400-e29b-41d4-a716-446655440002';

    private ResourceOwnerVoter             $voter;
    private TokenInterface&MockObject      $token;

    protected function setUp(): void
    {
        $this->voter = new ResourceOwnerVoter();
        $this->token = $this->createMock(TokenInterface::class);
    }

    public function test_vote_abstains_for_non_owner_access_attribute(): void
    {
        $result = $this->voter->vote($this->token, self::UUID_1, ['OTHER_ATTRIBUTE']);

        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function test_vote_abstains_for_non_string_subject(): void
    {
        $result = $this->voter->vote($this->token, null, [ResourceOwnerVoter::VIEW]);

        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    public function test_vote_denies_when_user_not_jwt_authenticated(): void
    {
        $user = $this->createMock(UserInterface::class);
        $this->token->method('getUser')->willReturn($user);

        $result = $this->voter->vote($this->token, self::UUID_1, [ResourceOwnerVoter::VIEW]);

        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function test_vote_grants_access_for_admin(): void
    {
        $user = $this->createMock(JwtAuthenticatedUserInterface::class);
        $user->method('getRoles')->willReturn(['ROLE_ADMIN']);
        $this->token->method('getUser')->willReturn($user);

        $result = $this->voter->vote($this->token, self::UUID_2, [ResourceOwnerVoter::VIEW]);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function test_vote_grants_access_for_resource_owner(): void
    {
        $user = $this->createMock(JwtAuthenticatedUserInterface::class);
        $user->method('getRoles')->willReturn(['ROLE_USER']);
        $user->method('getAuthUserId')->willReturn(self::UUID_1);
        $this->token->method('getUser')->willReturn($user);

        $result = $this->voter->vote($this->token, self::UUID_1, [ResourceOwnerVoter::VIEW]);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function test_vote_denies_access_for_different_user(): void
    {
        $user = $this->createMock(JwtAuthenticatedUserInterface::class);
        $user->method('getRoles')->willReturn(['ROLE_USER']);
        $user->method('getAuthUserId')->willReturn(self::UUID_1);
        $this->token->method('getUser')->willReturn($user);

        $result = $this->voter->vote($this->token, self::UUID_2, [ResourceOwnerVoter::VIEW]);

        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }
}
