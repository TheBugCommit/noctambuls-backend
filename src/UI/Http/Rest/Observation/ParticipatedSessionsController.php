<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Observation;

use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use App\Observation\Application\GetParticipatedSessions\GetParticipatedSessionsHandler;
use App\Observation\Application\GetParticipatedSessions\GetParticipatedSessionsRequest;
use App\Observation\Application\GetParticipatedSessions\GetParticipatedSessionsResponse;
use App\Profile\Application\GetProfile\GetProfileHandler;
use App\Profile\Application\GetProfile\GetProfileRequest;
use App\Profile\Domain\Exception\ProfileNotFoundException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class ParticipatedSessionsController
{
    public function __construct(
        private GetParticipatedSessionsHandler $handler,
        private GetProfileHandler              $profileHandler,
        private Security                       $security,
    ) {
    }

    public function __invoke(Request $request): GetParticipatedSessionsResponse
    {
        $offset = max(0, (int) $request->query->get('offset', 0));
        $limit  = max(1, min(100, (int) $request->query->get('limit', 20)));
        $since  = $this->parseSince($request->query->get('since'));

        /** @var AuthUserSymfonyAdapter $user */
        $user = $this->security->getUser();

        $profile = $this->profileHandler->handle(new GetProfileRequest($user->getAuthUserId()));

        return $this->handler->handle(new GetParticipatedSessionsRequest(
            ornithologistCode: $profile->ornithologistCode,
            offset: $offset,
            limit: $limit,
            since: $since,
        ));
    }

    private function parseSince(?string $sinceStr): ?\DateTimeImmutable
    {
        if ($sinceStr === null) {
            return null;
        }

        try {
            return (new \DateTimeImmutable($sinceStr))->setTimezone(new \DateTimeZone('UTC'));
        } catch (\Exception) {
            throw new BadRequestHttpException('Invalid since parameter');
        }
    }
}
