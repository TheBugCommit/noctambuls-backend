<?php

declare(strict_types=1);

namespace App\Tests\Observation\Domain\Entity;

use App\Observation\Domain\Entity\Session;
use App\Observation\Domain\Entity\SessionParticipant;
use App\Profile\Domain\ValueObject\OrnithologistCode;
use PHPUnit\Framework\TestCase;

class SessionParticipantTest extends TestCase
{
    public function test_it_creates_participant_successfully(): void
    {
        $sessionMock = $this->createMock(Session::class);
        $code = new OrnithologistCode('#OR-N-1234');

        $participant = SessionParticipant::create($sessionMock, $code);

        $this->assertNull($participant->id(), 'ID is initially null because it is auto-incremented by Doctrine');
        $this->assertSame($sessionMock, $participant->session());
        $this->assertEquals('#OR-N-1234', $participant->ornithologistCode()->value());
    }
}
