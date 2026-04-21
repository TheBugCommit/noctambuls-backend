<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Serializer\SerializerInterface;

#[AsEventListener(event: KernelEvents::VIEW)]
final readonly class ResponseSerializerListener
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function __invoke(ViewEvent $event): void
    {
        $result = $event->getControllerResult();

        if (is_object($result) && !$result instanceof JsonResponse) {
            $json = $this->serializer->serialize($result, 'json');
            $event->setResponse(new JsonResponse($json, 200, [], true));
        }
    }
}
