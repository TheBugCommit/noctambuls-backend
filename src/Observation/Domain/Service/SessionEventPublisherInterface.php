<?php

declare(strict_types=1);

namespace App\Observation\Domain\Service;

interface SessionEventPublisherInterface
{
    public function publishBeforeSessionsPurged(?\DateTimeImmutable $cutoff = null): void;
}
