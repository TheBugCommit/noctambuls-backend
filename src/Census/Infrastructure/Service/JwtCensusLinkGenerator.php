<?php

declare(strict_types=1);

namespace App\Census\Infrastructure\Service;

use App\Census\Domain\Entity\Census;
use App\Census\Domain\Service\CensusLinkGeneratorInterface;
use Firebase\JWT\JWT;

final readonly class JwtCensusLinkGenerator implements CensusLinkGeneratorInterface
{
    public function __construct(
        private string $appDomain,
        private string $jwtPrivateKeyPath,
        private string $jwtPassphrase
    ) {
    }

    public function generateFor(Census $census): string
    {
        $privateKeyContent = file_get_contents($this->jwtPrivateKeyPath);
        if (!$privateKeyContent) {
            throw new \RuntimeException('Cant read private KEY');
        }

        $privateKey = openssl_pkey_get_private($privateKeyContent, $this->jwtPassphrase);

        if (!$privateKey) {
            throw new \RuntimeException('Cant descifrate private KEY');
        }

        $fullDateTime = new \DateTimeImmutable($census->date()->format('Y-m-d') . ' ' . $census->time()->format('H:i:s'), new \DateTimeZone('UTC'));
        $datetimeString = $fullDateTime->format('c');

        $payload = [
            'i' => $census->id()->value(),
            't' => $census->title()->value(),
            's' => $datetimeString,
        ];

        $jwt = JWT::encode($payload, $privateKey, 'RS256');

        return sprintf('https://%s/census/join?token=%s', $this->appDomain, $jwt);
    }
}
