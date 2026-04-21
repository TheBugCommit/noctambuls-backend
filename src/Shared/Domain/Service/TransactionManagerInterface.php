<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

interface TransactionManagerInterface
{
    public function begin(): void;
    public function commit(): void;
    public function rollback(): void;
}
