<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Profile;

use App\Auth\Infrastructure\Security\ResourceOwnerVoter;
use App\Profile\Application\GetProfile\GetProfileHandler;
use App\Profile\Application\GetProfile\GetProfileRequest;
use App\Profile\Application\GetProfile\GetProfileResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class GetProfileController
{
    public function __construct(
        private GetProfileHandler $handler,
        private Security          $security,
    ) {
    }

    public function __invoke(Request $request, string $id): GetProfileResponse
    {
        if (!$this->security->isGranted(ResourceOwnerVoter::VIEW, $id)) {
            throw new AccessDeniedHttpException();
        }

        return $this->handler->handle(new GetProfileRequest($id));
    }
}
