<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Observation;

use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use App\Observation\Application\SyncUp\SyncUpHandler;
use App\Observation\Application\SyncUp\SyncUpRequest;
use App\Observation\Application\SyncUp\SyncUpResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final readonly class SyncUpController
{
    public function __construct(
        private SyncUpHandler $handler,
        private Security      $security,
    )
    {
    }

    public function __invoke(Request $request): SyncUpResponse
    {
        $body = json_decode($request->getContent(), true);

        if (!is_array($body) || !isset($body['sessions']) || !is_array($body['sessions'])) {
            throw new BadRequestHttpException('Invalid payload');
        }

        if (count($body['sessions']) > 50) {
            throw new HttpException(
                413,
                'Content too large',
            );
        }

        /** @var AuthUserSymfonyAdapter $user */
        $user = $this->security->getUser();

        return $this->handler->handle(new SyncUpRequest(
            annotatorId: $user->getAuthUserId(),
            sessions: $body['sessions'],
        ));
    }
}
