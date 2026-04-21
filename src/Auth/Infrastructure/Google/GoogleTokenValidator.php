<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Google;

use App\Auth\Domain\Exception\InvalidExternalTokenException;
use App\Auth\Domain\Service\ExternalTokenValidatorInterface;
use App\Auth\Domain\ValueObject\Email;
use Google\Client as GoogleClient;

final readonly class GoogleTokenValidator implements ExternalTokenValidatorInterface
{
    public function __construct(
        private GoogleClient $googleClient,
    ) {
    }

    public function validate(string $token): Email
    {
        $payload = $this->googleClient->verifyIdToken($token);

        if (!$payload) {
            throw new InvalidExternalTokenException('Invalid Google token.');
        }

        if (($payload['email_verified'] ?? false) !== true) {
            throw new InvalidExternalTokenException('Google account email is not verified.');
        }

        if (empty($payload['email'])) {
            throw new InvalidExternalTokenException('Email not present in Google token.');
        }

        return new Email($payload['email']);
    }
}
