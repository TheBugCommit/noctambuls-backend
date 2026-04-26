<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\UpdateBird;

use App\BirdCatalog\Application\Service\BirdCatalogUpdaterService;
use App\BirdCatalog\Domain\Exception\BirdNotFoundException;
use App\BirdCatalog\Domain\Repository\BirdRepositoryInterface;
use App\BirdCatalog\Domain\ValueObject\BirdDescription;
use App\BirdCatalog\Domain\ValueObject\BirdId;
use App\BirdCatalog\Domain\ValueObject\CommonName;
use App\BirdCatalog\Domain\ValueObject\InformationUrl;
use App\BirdCatalog\Domain\ValueObject\ScientificName;
use App\Shared\Application\Service\AudioUploaderInterface;
use App\Shared\Application\Service\FileRemoverInterface;
use App\Shared\Application\Service\ImageUploaderInterface;
use Throwable;

final readonly class UpdateBirdHandler
{
    public function __construct(
        private BirdRepositoryInterface               $birdRepository,
        private ImageUploaderInterface                $imageUploader,
        private AudioUploaderInterface                $audioUploader,
        private FileRemoverInterface                  $fileRemover,
        private BirdCatalogUpdaterService             $updateBird,
    ) {
    }

    /** @throws Throwable */
    public function handle(UpdateBirdRequest $request): void
    {
        $bird = $this->birdRepository->findById(new BirdId($request->id));
        if ($bird === null) {
            throw new BirdNotFoundException();
        }

        $oldImagePath = $bird->imagePath();
        $oldAudioPath = $bird->audioPath();

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
                $finalImagePath,
                $finalAudioPath,
                new InformationUrl($request->informationUrl),
            );

            foreach ($request->translations as $translationInput) {
                $bird->updateTranslation(
                    $translationInput->languageCode,
                    new CommonName($translationInput->commonName),
                    new BirdDescription($translationInput->description)
                );
            }

            $this->updateBird->save($bird);

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
