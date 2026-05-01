<?php

declare(strict_types=1);

namespace App\Profile\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;

final class ProfileNotFoundException extends \DomainException implements DomainExceptionInterface
{
    public function __construct(string $message = 'Profile not found.')
    {
        parent::__construct($message);
    }

    public function type(): ErrorType
    {
        return ErrorType::NOT_FOUND;
    }
}
