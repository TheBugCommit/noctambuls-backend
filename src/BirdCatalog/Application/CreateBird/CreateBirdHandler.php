<?php

declare(strict_types=1);

namespace App\BirdCatalog\Application\CreateBird;

use App\BirdCatalog\Domain\Entity\Bird;
use App\BirdCatalog\Domain\Entity\BirdTranslation;
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

final readonly class CreateBirdHandler
{
    public function __construct(
        private BirdRepositoryInterface               $birdRepository,
        private BirdCatalogVersionRepositoryInterface $versionRepository,
        private TransactionManagerInterface           $transactionManager,
        private UuidGeneratorInterface                $uuidGenerator,
        private ImageUploaderInterface                $imageUploader,
        private AudioUploaderInterface                $audioUploader,
        private FileRemoverInterface                  $fileRemover,
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


            $bird = Bird::create(
                new BirdId($this->uuidGenerator->generate()),
                new ScientificName($request->scientificName),
                $imagePath,
                $audioPath,
                new InformationUrl($request->informationUrl),
            );

            foreach (LanguageCode::cases() as $lang) {
                $langCode = $lang->value;

                if (!isset($request->translations[$langCode])) {
                    throw new \InvalidArgumentException("Mising data for lang $langCode");
                }

                $bird->addTranslation(new BirdTranslation(
                    new BirdTranslationId($this->uuidGenerator->generate()),
                    $bird,
                    $lang,
                    new CommonName($request->translations[$langCode]['commonName']),
                    new BirdDescription($request->translations[$langCode]['description']),
                ));
            }

            $bird->assertHasRequiredTranslations();


            $catalogVersion = $this->versionRepository->get();

            $this->transactionManager->begin();
            try {
                $this->birdRepository->save($bird);
                $catalogVersion->increment();
                $this->versionRepository->save($catalogVersion);
                $this->transactionManager->commit();
            } catch (Throwable $exception) {
                $this->transactionManager->rollback();
                throw $exception;
            }
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                $this->fileRemover->remove($imagePath);
            }
            if ($audioPath !== null) {
                $this->fileRemover->remove($audioPath);
            }

            throw $exception;
        }
        return new CreateBirdResponse($bird->id()->value());
    }
}
