<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;

final class UserBlockedException extends \DomainException implements DomainExceptionInterface
{
    public function __construct(string $message = 'User account is blocked.')
    {
        parent::__construct($message);
    }

    public function type(): ErrorType
    {
        return ErrorType::UNAUTHORIZED;
    }
}
