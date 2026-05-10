<?php

declare(strict_types=1);

namespace App\Census\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;

final class CensusNotFoundException extends \DomainException implements DomainExceptionInterface
{
    public function __construct(string $message = 'Census not found.')
    {
        parent::__construct($message);
    }

    public function type(): ErrorType
    {
        return ErrorType::NOT_FOUND;
    }
}
