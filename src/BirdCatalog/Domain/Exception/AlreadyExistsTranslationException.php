<?php

declare(strict_types=1);

namespace App\BirdCatalog\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;

final class AlreadyExistsTranslationException extends \DomainException implements DomainExceptionInterface
{
    public function __construct(string $message = 'Already exists translation.')
    {
        parent::__construct($message);
    }

    public function type(): ErrorType
    {
        return ErrorType::INVALID_ARGUMENT;
    }
}
