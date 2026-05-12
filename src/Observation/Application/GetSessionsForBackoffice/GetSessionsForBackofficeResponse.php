<?php

declare(strict_types=1);

namespace App\Observation\Application\GetSessionsForBackoffice;

final readonly class GetSessionsForBackofficeResponse
{
    public function __construct(
        public array $sessions,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }

    public function totalPages(): int
    {
        return $this->limit > 0 ? (int)ceil($this->total / $this->limit) : 1;
    }
}
