<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Application\Service\QrCodeGeneratorInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Exception\ValidationException;
use Endroid\QrCode\Writer\PngWriter;

final class EndroidQrCodeGenerator implements QrCodeGeneratorInterface
{
    public function generateBase64(string $data): string
    {
        try {
            $builder = new Builder(
                writer: new PngWriter(),
                data: $data,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 1000,
                margin: 10
            );

            $result = $builder->build();
        } catch (ValidationException $e) {
            throw new \RuntimeException(
                'Failed to generate QR code: ' . $e->getMessage(),
                previous: $e
            );
        }

        return $result->getDataUri();
    }
}
