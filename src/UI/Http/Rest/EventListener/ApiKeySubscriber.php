<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class ApiKeySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private string $apiKey
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        $path = $request->getPathInfo();

        if (!str_starts_with($path, '/api') || str_starts_with($path, '/api/internal')) {
            return;
        }

        $providedApiKey = $request->headers->get('X-API-KEY');

        if (empty($providedApiKey) || $providedApiKey !== $this->apiKey) {
            throw new UnauthorizedHttpException('Bearer', 'Missing or Invalid API Key');
        }
    }
}
