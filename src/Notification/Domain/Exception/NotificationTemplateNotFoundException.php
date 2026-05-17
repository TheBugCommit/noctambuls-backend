<?php

declare(strict_types=1);

namespace App\Notification\Domain\Exception;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;

final class NotificationTemplateNotFoundException extends \DomainException implements DomainExceptionInterface
{
    public function __construct(string $message = 'Notification template not found.')
    {
        parent::__construct($message);
    }

    public function type(): ErrorType
    {
        return ErrorType::NOT_FOUND;
    }
}
