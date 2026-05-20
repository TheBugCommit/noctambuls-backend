<?php

declare(strict_types=1);

namespace App\Tests\Auth\Infrastructure\Security;

use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use App\Auth\Infrastructure\Security\SymfonyPasswordHasher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface as SymfonyPasswordHasherInterface;

final class SymfonyPasswordHasherTest extends TestCase
{
    private PasswordHasherFactoryInterface&MockObject $hasherFactory;
    private SymfonyPasswordHasher                    $passwordHasher;

    protected function setUp(): void
    {
        $this->hasherFactory  = $this->createMock(PasswordHasherFactoryInterface::class);
        $this->passwordHasher = new SymfonyPasswordHasher($this->hasherFactory);
    }

    public function test_hash_delegates_to_factory_hasher(): void
    {
        $hasher = $this->createMock(SymfonyPasswordHasherInterface::class);
        $hasher->method('hash')->willReturn('hashed_result');

        $this->hasherFactory->method('getPasswordHasher')->willReturn($hasher);

        $result = $this->passwordHasher->hash('plain_password');

        self::assertSame('hashed_result', $result);
    }

    public function test_hash_passes_class_name_to_factory(): void
    {
        $hasher = $this->createMock(SymfonyPasswordHasherInterface::class);
        $hasher->method('hash')->willReturn('hashed');

        $this->hasherFactory
            ->expects(self::once())
            ->method('getPasswordHasher')
            ->with(AuthUserSymfonyAdapter::class)
            ->willReturn($hasher);

        $this->passwordHasher->hash('plain');
    }
}
