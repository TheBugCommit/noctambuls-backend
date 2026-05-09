<?php

declare(strict_types=1);

namespace App\Profile\Domain\Entity;

use App\Profile\Domain\ValueObject\OrnithologistCode;
use App\Profile\Domain\ValueObject\PhoneNumber;
use App\Profile\Domain\ValueObject\ProfileId;
use App\Profile\Domain\ValueObject\ProfileName;

class Profile
{
    public function __construct(
        private readonly ProfileId          $id,
        private ProfileName                 $name,
        private PhoneNumber                 $phone,
        private readonly OrnithologistCode  $ornithologistCode,
        private readonly \DateTimeImmutable $createdAt,
    )
    {
    }

    public static function create(
        ProfileId         $id,
        ProfileName       $name,
        PhoneNumber       $phone,
        OrnithologistCode $ornithologistCode,
    ): self
    {
        return new self($id, $name, $phone, $ornithologistCode, new \DateTimeImmutable());
    }

    public function update(ProfileName $name, PhoneNumber $phone): void
    {
        $this->name = $name;
        $this->phone = $phone;
    }

    public function anonymize(): void
    {
        $this->name = new ProfileName('Deleted User');
        $this->phone = new PhoneNumber('+000000000');
    }

    public function id(): ProfileId
    {
        return $this->id;
    }

    public function name(): ProfileName
    {
        return $this->name;
    }

    public function phone(): PhoneNumber
    {
        return $this->phone;
    }

    public function ornithologistCode(): OrnithologistCode
    {
        return $this->ornithologistCode;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
