<?php

namespace App\Profile\Application\Mapper;

use App\Profile\Application\EditProfile\EditProfileResponse;
use App\Profile\Application\GetProfile\GetProfileResponse;
use App\Profile\Domain\Entity\Profile;

class ProfileResponseMapper
{
    public static function toGetProfileResponse(Profile $profile): GetProfileResponse
    {
        return new GetProfileResponse(
            $profile->id()->value(),
            $profile->name()->value(),
            $profile->phone()->value(),
            $profile->ornithologistCode()->value(),
            $profile->createdAt(),
        );
    }

    public static function toEditProfileResponse(Profile $profile): EditProfileResponse
    {
        return new EditProfileResponse(
            $profile->id()->value(),
            $profile->name()->value(),
            $profile->phone()->value(),
            $profile->ornithologistCode()->value(),
            $profile->createdAt(),
        );
    }
}
