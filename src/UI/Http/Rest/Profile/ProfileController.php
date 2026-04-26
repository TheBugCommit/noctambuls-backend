<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Profile;

use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class ProfileController
{
    public function __construct(
        private  Security $security,
        private AuthUserRepositoryInterface $userRepository,
        private SerializerInterface $serializer,
    ) {

    }
    public function __invoke(Request $request): JsonResponse
    {
        $id = new AuthUserId($this->security->getUser()->getUserIdentifier());
        $user = $this->userRepository->findById($id);

        return new JsonResponse([
            'success' => true,
            'request' => $request,
            'user_id'  => $this->security->getUser()->getUserIdentifier(),
            'user'     => $this->serializer->serialize($user, 'json'),
        ]);
    }
}
