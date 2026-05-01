<?php

declare(strict_types=1);

namespace App\Profile\Application\GetProfile;

use App\Profile\Domain\Exception\ProfileNotFoundException;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\ProfileId;

final readonly class GetProfileHandler
{
    public function __construct(
        private ProfileRepositoryInterface $profileRepository,
    ) {
    }

    public function handle(GetProfileRequest $request): GetProfileResponse
    {
        $profile = $this->profileRepository->findById(new ProfileId($request->id));

        if ($profile === null) {
            throw new ProfileNotFoundException();
        }

        return new GetProfileResponse(
            $profile->id()->value(),
            $profile->name()->value(),
            $profile->phone()->value(),
            $profile->createdAt(),
        );
    }
}
