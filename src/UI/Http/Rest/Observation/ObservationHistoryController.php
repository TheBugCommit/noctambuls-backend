<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Observation;

use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use App\Observation\Application\GetObservationHistory\GetObservationHistoryHandler;
use App\Observation\Application\GetObservationHistory\GetObservationHistoryRequest;
use App\Observation\Application\GetObservationHistory\GetObservationHistoryResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

final readonly class ObservationHistoryController
{
    public function __construct(
        private GetObservationHistoryHandler $handler,
        private Security                     $security,
    ) {
    }

    public function __invoke(Request $request): GetObservationHistoryResponse
    {
        $offset = max(0, (int) $request->query->get('offset', 0));
        $limit  = max(1, min(100, (int) $request->query->get('limit', 20)));

        /** @var AuthUserSymfonyAdapter $user */
        $user = $this->security->getUser();

        return $this->handler->handle(new GetObservationHistoryRequest(
            annotatorId: $user->getAuthUserId(),
            offset: $offset,
            limit: $limit,
        ));
    }
}
