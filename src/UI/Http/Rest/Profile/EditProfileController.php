<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Profile;

use App\Auth\Infrastructure\Security\ResourceOwnerVoter;
use App\Profile\Application\EditProfile\EditProfileHandler;
use App\Profile\Application\EditProfile\EditProfileRequest;
use App\Profile\Application\EditProfile\EditProfileResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class EditProfileController
{
    public function __construct(
        private EditProfileHandler $handler,
        private Security           $security,
    ) {
    }

    public function __invoke(Request $request, string $id): EditProfileResponse
    {
        if (!$this->security->isGranted(ResourceOwnerVoter::VIEW, $id)) {
            throw new AccessDeniedHttpException();
        }

        $body  = json_decode($request->getContent(), true);
        $name  = is_array($body) ? ($body['name']  ?? null) : null;
        $phone = is_array($body) ? ($body['phone'] ?? null) : null;

        if ($name !== null && (!is_string($name) || trim($name) === '')) {
            throw new \InvalidArgumentException('Field "name" must be a non-empty string.');
        }
        if ($phone !== null && (!is_string($phone) || trim($phone) === '')) {
            throw new \InvalidArgumentException('Field "phone" must be a non-empty string.');
        }
        if ($name === null && $phone === null) {
            throw new \InvalidArgumentException('At least one of "name" or "phone" must be provided.');
        }

        return $this->handler->handle(new EditProfileRequest($id, $name, $phone));
    }
}
