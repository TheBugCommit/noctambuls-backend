<?php
declare(strict_types=1);

namespace App\UI\Http\Backoffice\BirdCatalog\Mapper;

use App\BirdCatalog\Application\BirdTranslation\BirdTranslationRequest;
use App\BirdCatalog\Domain\ValueObject\LanguageCode;
use App\Shared\Application\DTO\UploadFileRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class BirdFormMapper
{
    /** @return BirdTranslationRequest[] */
    public static function mapTranslations(array $raw): array
    {
        $translations = [];

        foreach (LanguageCode::cases() as $lang) {
            $langCode = $lang->value;

            if (isset($raw[$langCode])) {
                $translations[] = new BirdTranslationRequest(
                    $lang,
                    trim((string) ($raw[$langCode]['commonName'] ?? '')),
                    trim((string) ($raw[$langCode]['description'] ?? ''))
                );
            }
        }

        return $translations;
    }

    public static function mapFile(?UploadedFile $file): ?UploadFileRequest
    {
        if (!$file instanceof UploadedFile) {
            return null;
        }

        return new UploadFileRequest(
            $file->getPathname(),
            $file->getClientOriginalName(),
            $file->guessExtension() ?? $file->getClientOriginalExtension(),
            $file->getSize()
        );
    }
}
