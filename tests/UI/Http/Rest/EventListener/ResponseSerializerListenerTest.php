<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\EventListener;

use App\UI\Http\Rest\EventListener\ResponseSerializerListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ResponseSerializerListenerTest extends TestCase
{
    private SerializerInterface&MockObject $serializer;
    private ResponseSerializerListener $listener;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->listener   = new ResponseSerializerListener($this->serializer);
    }

    public function test_serializes_object_result_to_json_response(): void
    {
        $this->serializer->method('serialize')->willReturn('{"foo":"bar"}');

        $event = $this->makeEvent(new \stdClass());
        ($this->listener)($event);

        self::assertInstanceOf(JsonResponse::class, $event->getResponse());
    }

    public function test_response_has_200_status_code(): void
    {
        $this->serializer->method('serialize')->willReturn('{}');

        $event = $this->makeEvent(new \stdClass());
        ($this->listener)($event);

        self::assertSame(200, $event->getResponse()->getStatusCode());
    }

    public function test_response_body_is_serializer_output(): void
    {
        $payload = '{"access_token":"jwt"}';
        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->willReturn($payload);

        $event = $this->makeEvent(new \stdClass());
        ($this->listener)($event);

        self::assertSame($payload, $event->getResponse()->getContent());
    }

    public function test_serializer_is_called_with_json_format(): void
    {
        $result = new \stdClass();
        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->with($result, 'json')
            ->willReturn('{}');

        ($this->listener)($this->makeEvent($result));
    }

    public function test_skips_null_result(): void
    {
        $this->serializer->expects(self::never())->method('serialize');

        $event = $this->makeEvent(null);
        ($this->listener)($event);

        self::assertNull($event->getResponse());
    }

    public function test_skips_string_result(): void
    {
        $this->serializer->expects(self::never())->method('serialize');

        $event = $this->makeEvent('plain string');
        ($this->listener)($event);

        self::assertNull($event->getResponse());
    }

    public function test_skips_json_response_result(): void
    {
        $this->serializer->expects(self::never())->method('serialize');

        $event = $this->makeEvent(new JsonResponse(['already' => 'done']));
        ($this->listener)($event);

        self::assertNull($event->getResponse());
    }

    private function makeEvent(mixed $result): ViewEvent
    {
        $kernel  = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/api/foo');

        return new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $result);
    }
}
