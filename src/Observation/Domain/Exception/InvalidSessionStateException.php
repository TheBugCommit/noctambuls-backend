<?php

namespace App\Observation\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;

class InvalidSessionStateException extends \DomainException implements DomainExceptionInterface
{
    public function __construct(string $message = 'Invalid session state.')
    {
        parent::__construct($message);
    }

    public function type(): ErrorType
    {
        return ErrorType::INVALID_ARGUMENT;
    }
}
