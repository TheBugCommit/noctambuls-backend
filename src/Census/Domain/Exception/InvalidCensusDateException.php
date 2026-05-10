<?php

declare(strict_types=1);

namespace App\Census\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;

final class InvalidCensusDateException extends \DomainException implements DomainExceptionInterface
{
    public function __construct(string $date)
    {
        parent::__construct(sprintf('The census date "%s" is invalid. Expected format: Y-m-d.', $date));
    }

    public function type(): ErrorType
    {
        return ErrorType::INVALID_ARGUMENT;
    }
}
