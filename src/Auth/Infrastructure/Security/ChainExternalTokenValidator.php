<?php

namespace App\Auth\Infrastructure\Security;

use App\Auth\Domain\Service\ExternalTokenValidatorInterface;
use App\Auth\Domain\Service\ProviderTokenValidatorInterface;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider;

final readonly class ChainExternalTokenValidator implements ExternalTokenValidatorInterface
{
    /**
     * @param iterable<ProviderTokenValidatorInterface> $validators
     */
    public function __construct(
        private iterable $validators
    ) {
    }

    public function validate(string $token, Provider $provider): Email
    {
        foreach ($this->validators as $validator) {
            if ($validator->supports($provider)) {
                return $validator->validate($token);
            }
        }

        throw new \InvalidArgumentException(sprintf('No validator found for provider "%s"', $provider->value()));
    }
}
