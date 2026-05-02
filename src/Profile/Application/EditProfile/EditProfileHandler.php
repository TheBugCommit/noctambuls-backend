<?php

declare(strict_types=1);

namespace App\Profile\Application\EditProfile;

use App\Profile\Application\Mapper\ProfileResponseMapper;
use App\Profile\Domain\Exception\ProfileNotFoundException;
use App\Profile\Domain\Repository\ProfileRepositoryInterface;
use App\Profile\Domain\ValueObject\PhoneNumber;
use App\Profile\Domain\ValueObject\ProfileId;
use App\Profile\Domain\ValueObject\ProfileName;
use App\Shared\Domain\Service\TransactionManagerInterface;
use Throwable;

final readonly class EditProfileHandler
{
    public function __construct(
        private ProfileRepositoryInterface  $profileRepository,
        private TransactionManagerInterface $transactionManager,
        private ProfileResponseMapper       $mapper
    ) {
    }

    /** @throws Throwable */
    public function handle(EditProfileRequest $request): EditProfileResponse
    {
        $profile = $this->profileRepository->findById(new ProfileId($request->id));

        if ($profile === null) {
            throw new ProfileNotFoundException();
        }

        $newName  = $request->name  !== null ? new ProfileName($request->name)  : $profile->name();
        $newPhone = $request->phone !== null ? new PhoneNumber($request->phone) : $profile->phone();

        $this->transactionManager->begin();
        try {
            $profile->update($newName, $newPhone);
            $this->profileRepository->save($profile);
            $this->transactionManager->commit();
        } catch (Throwable $e) {
            $this->transactionManager->rollback();
            throw $e;
        }


        return $this->mapper->toEditProfileResponse($profile);
    }
}
