<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Uid;

use App\Shared\Domain\Service\UuidGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class SymfonyUuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
