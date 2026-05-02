<?php

declare(strict_types=1);

namespace App\Profile\Application\EditProfile;

readonly class EditProfileRequest
{
    public function __construct(
        public string  $id,
        public ?string $name,
        public ?string $phone,
    ) {
    }
}
