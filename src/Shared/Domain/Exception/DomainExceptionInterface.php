<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

interface DomainExceptionInterface extends \Throwable
{
    public function type(): ErrorType;
}
