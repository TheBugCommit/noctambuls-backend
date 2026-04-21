<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;

final class InvalidRefreshTokenException extends \DomainException implements DomainExceptionInterface
{
    public function __construct(protected $message = 'Refresh token is invalid.') {
        parent::__construct($message);
    }

    public function type(): ErrorType
    {
        return ErrorType::UNAUTHORIZED;
    }
}
