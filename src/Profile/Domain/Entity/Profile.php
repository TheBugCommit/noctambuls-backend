<?php

declare(strict_types=1);

namespace App\Profile\Domain\Entity;

use App\Profile\Domain\ValueObject\PhoneNumber;
use App\Profile\Domain\ValueObject\ProfileId;
use App\Profile\Domain\ValueObject\ProfileName;

readonly class Profile
{
    public function __construct(
        private ProfileId          $id,
        private ProfileName        $name,
        private PhoneNumber        $phone,
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        ProfileId   $id,
        ProfileName $name,
        PhoneNumber $phone,
    ): self {
        return new self($id, $name, $phone, new \DateTimeImmutable());
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

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
