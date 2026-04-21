<?php
declare(strict_types=1);

namespace App\Shared\Domain\Exception;

enum ErrorType
{
    case NOT_FOUND;
    case UNAUTHORIZED;
    case FORBIDDEN;
    case CONFLICT;
    case INVALID_ARGUMENT;
    case INTERNAL_SERVER_ERROR;
}
