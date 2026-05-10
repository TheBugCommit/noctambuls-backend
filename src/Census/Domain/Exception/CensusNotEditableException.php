<?php

declare(strict_types=1);

namespace App\Census\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;

final class CensusNotEditableException extends \DomainException implements DomainExceptionInterface
{
    public function __construct(string $message = 'Census cannot be edited or deleted after the 10-minute grace period.')
    {
        parent::__construct($message);
    }

    public function type(): ErrorType
    {
        return ErrorType::FORBIDDEN;
    }
}
