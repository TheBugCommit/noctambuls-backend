<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\UpdateBird;

use App\BirdCatalog\Domain\Entity\BirdTranslation;
use App\BirdCatalog\Domain\Exception\BirdNotFoundException;
use App\BirdCatalog\Domain\Repository\BirdCatalogVersionRepositoryInterface;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\BirdCatalog\Domain\ValueObject\BirdDescription;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\BirdCatalog\Domain\ValueObject\BirdTranslationId;
use App\BirdCatalog\Domain\ValueObject\CommonName;
use App\BirdCatalog\Domain\ValueObject\InformationUrl;
use App\BirdCatalog\Domain\ValueObject\LanguageCode;
use App\BirdCatalog\Domain\ValueObject\ScientificName;
use App\Shared\Application\Service\AudioUploaderInterface;
use App\Shared\Application\Service\FileRemoverInterface;
use App\Shared\Application\Service\ImageUploaderInterface;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use App\Shared\Domain\ValueObject\AudioPath;
use App\Shared\Domain\ValueObject\ImagePath;
use Throwable;

final readonly class UpdateBirdHandler
{
    public function __construct(
        private BirdRepositoryInterface               $birdRepository,
        private BirdCatalogVersionRepositoryInterface $versionRepository,
        private TransactionManagerInterface           $transactionManager,
        private ImageUploaderInterface                $imageUploader,
        private AudioUploaderInterface                $audioUploader,
        private FileRemoverInterface                  $fileRemover,
    ) {
    }

    /** @throws Throwable */
    public function handle(UpdateBirdRequest $request): void
    {
        $bird = $this->birdRepository->findById(new BirdId($request->id));
        if ($bird === null) {
            throw new BirdNotFoundException();
        }

        $oldImagePath = $bird->imagePath()->value();
        $oldAudioPath = $bird->audioPath()->value();

        $newImagePath = null;
        $newAudioPath = null;

        $finalImagePath = $oldImagePath;
        $finalAudioPath = $oldAudioPath;

        try {
            if ($request->image !== null) {
                $newImagePath = $this->imageUploader->uploadImage($request->image);
                $finalImagePath = $newImagePath;
            }
            if ($request->audio !== null) {
                $newAudioPath = $this->audioUploader->uploadAudio($request->audio);
                $finalAudioPath = $newAudioPath;
            }

            $bird->update(
                new ScientificName($request->scientificName),
                new ImagePath($finalImagePath),
                new AudioPath($finalAudioPath),
                new InformationUrl($request->informationUrl),
            );

            foreach (LanguageCode::cases() as $lang) {
                $langCode = $lang->value;

                if (!isset($request->translations[$langCode])) {
                    continue;
                }

                $bird->updateTranslation(
                    $lang,
                    new CommonName($request->translations[$langCode]['commonName']),
                    new BirdDescription($request->translations[$langCode]['description'])
                );
            }

            $catalogVersion = $this->versionRepository->get();

            $this->transactionManager->begin();
            try {
                $this->birdRepository->save($bird);
                $catalogVersion->increment();
                $this->versionRepository->save($catalogVersion);
                $this->transactionManager->commit();
            } catch (Throwable $e) {
                $this->transactionManager->rollback();
                throw $e;
            }

            if ($newImagePath !== null && $oldImagePath !== '') {
                $this->fileRemover->remove($oldImagePath);
            }
            if ($newAudioPath !== null && $oldAudioPath !== '') {
                $this->fileRemover->remove($oldAudioPath);
            }

        } catch (Throwable $exception) {
            if ($newImagePath !== null) {
                $this->fileRemover->remove($newImagePath);
            }
            if ($newAudioPath !== null) {
                $this->fileRemover->remove($newAudioPath);
            }

            throw $exception;
        }
    }
}
