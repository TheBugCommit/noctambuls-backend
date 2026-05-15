<?php

namespace App\Observation\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;

class InvalidObservationStateException extends \DomainException implements DomainExceptionInterface
{
    public function __construct(string $message = 'Invalid observation state.')
    {
        parent::__construct($message);
    }

    public function type(): ErrorType
    {
        return ErrorType::INVALID_ARGUMENT;
    }
}
