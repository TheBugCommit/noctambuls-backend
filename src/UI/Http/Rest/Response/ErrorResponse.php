<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Response;

final readonly class ErrorResponse
{
    public function __construct(
        public string $message,
        public string $code,
        public array $details = []
    ) {
    }
}
