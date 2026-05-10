<?php

declare(strict_types=1);

namespace App\Census\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;

final class InvalidCensusTimeException extends \DomainException implements DomainExceptionInterface
{
    public function __construct(string $time)
    {
        parent::__construct(sprintf('The census time "%s" is invalid. Expected format: H:i.', $time));
    }

    public function type(): ErrorType
    {
        return ErrorType::INVALID_ARGUMENT;
    }
}
