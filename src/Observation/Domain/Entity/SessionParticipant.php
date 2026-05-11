<?php

declare(strict_types=1);

namespace App\Observation\Domain\Entity;

use App\Profile\Domain\ValueObject\OrnithologistCode;

class SessionParticipant
{
    private ?int $id = null;

    public function __construct(
        private readonly Session           $session,
        private readonly OrnithologistCode $ornithologistCode,
    )
    {
    }

    public static function create(Session $session, OrnithologistCode $ornithologistCode): self
    {
        return new self($session, $ornithologistCode);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function session(): Session
    {
        return $this->session;
    }

    public function ornithologistCode(): OrnithologistCode
    {
        return $this->ornithologistCode;
    }
}
