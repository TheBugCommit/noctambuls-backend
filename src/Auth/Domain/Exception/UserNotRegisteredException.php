<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;

final class UserNotRegisteredException extends \DomainException implements DomainExceptionInterface
{
    public function __construct(string $message = 'User is not registered.')
    {
        parent::__construct($message);
    }

    public function type(): ErrorType
    {
        return ErrorType::NOT_FOUND;
    }
}
