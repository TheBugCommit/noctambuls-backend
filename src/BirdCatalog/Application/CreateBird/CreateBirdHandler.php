<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\CreateBird;

use App\BirdCatalog\Application\Mapper\BirdResponseMapper;
use App\BirdCatalog\Application\Service\BirdCatalogUpdaterService;
use App\BirdCatalog\Application\Service\BirdService;
use App\Shared\Application\Service\AudioUploaderInterface;
use App\Shared\Application\Service\FileRemoverInterface;
use App\Shared\Application\Service\ImageUploaderInterface;
use Throwable;

final readonly class CreateBirdHandler
{
    public function __construct(
        private ImageUploaderInterface                $imageUploader,
        private AudioUploaderInterface                $audioUploader,
        private FileRemoverInterface                  $fileRemover,
        private BirdService                           $birdService,
        private BirdCatalogUpdaterService             $birdCatalogUpdaterService,
    ) {
    }

    /** @throws Throwable */
    public function handle(CreateBirdRequest $request): CreateBirdResponse
    {
        $imagePath = null;
        $audioPath = null;
        try {
            $imagePath = $this->imageUploader->uploadImage($request->image);
            $audioPath = $this->audioUploader->uploadAudio($request->audio);

            $bird = $this->birdService->create(
                $request->scientificName,
                $imagePath,
                $audioPath,
                $request->informationUrl,
                $request->translations
            );

            $this->birdCatalogUpdaterService->save($bird);
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                $this->fileRemover->remove($imagePath);
            }
            if ($audioPath !== null) {
                $this->fileRemover->remove($audioPath);
            }

            throw $exception;
        }

        return BirdResponseMapper::toCreateBirdResponse($bird);
    }
}
