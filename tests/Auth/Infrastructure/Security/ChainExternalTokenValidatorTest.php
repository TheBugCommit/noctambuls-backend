<?php

declare(strict_types=1);

namespace App\Tests\Auth\Infrastructure\Security;

use App\Auth\Domain\Service\ProviderTokenValidatorInterface;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Infrastructure\Security\ChainExternalTokenValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ChainExternalTokenValidatorTest extends TestCase
{
    public function test_validate_calls_matching_validator(): void
    {
        $email     = new Email('user@example.com');
        $provider  = Provider::google();
        $validator = $this->createMock(ProviderTokenValidatorInterface::class);

        $validator->method('supports')->willReturn(true);
        $validator->expects(self::once())->method('validate')->with('token123')->willReturn($email);

        $chain    = new ChainExternalTokenValidator([$validator]);
        $result   = $chain->validate('token123', $provider);

        self::assertSame('user@example.com', $result->value());
    }

    public function test_validate_skips_non_supporting_validator(): void
    {
        $email      = new Email('user@example.com');
        $provider   = Provider::google();

        /** @var ProviderTokenValidatorInterface&MockObject $first */
        $first = $this->createMock(ProviderTokenValidatorInterface::class);
        $first->method('supports')->willReturn(false);
        $first->expects(self::never())->method('validate');

        /** @var ProviderTokenValidatorInterface&MockObject $second */
        $second = $this->createMock(ProviderTokenValidatorInterface::class);
        $second->method('supports')->willReturn(true);
        $second->expects(self::once())->method('validate')->willReturn($email);

        $chain  = new ChainExternalTokenValidator([$first, $second]);
        $result = $chain->validate('token', $provider);

        self::assertSame('user@example.com', $result->value());
    }

    public function test_validate_throws_when_no_validator_found(): void
    {
        $validator = $this->createMock(ProviderTokenValidatorInterface::class);
        $validator->method('supports')->willReturn(false);

        $chain = new ChainExternalTokenValidator([$validator]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No validator found for provider "google"');

        $chain->validate('token', Provider::google());
    }
}
