<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\EventListener;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;
use App\UI\Http\Rest\EventListener\ApiExceptionListener;
use App\UI\Http\Rest\Response\ErrorResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ApiExceptionListenerTest extends TestCase
{
    private SerializerInterface&MockObject $serializer;
    private ApiExceptionListener $listener;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->serializer->method('serialize')->willReturn('{}');
        $this->listener   = new ApiExceptionListener($this->serializer, false);
    }

    public function test_skips_non_api_path(): void
    {
        $this->serializer->expects(self::never())->method('serialize');

        $event = $this->makeEvent('/backoffice/login', new \RuntimeException('boom'));
        ($this->listener)($event);

        self::assertNull($event->getResponse());
    }

    #[DataProvider('domainErrorTypes')]
    public function test_domain_exception_maps_to_correct_status(ErrorType $type, int $expectedStatus): void
    {
        $event = $this->makeEvent('/api/any', $this->makeDomainException($type));
        ($this->listener)($event);

        self::assertSame($expectedStatus, $event->getResponse()->getStatusCode());
    }

    public static function domainErrorTypes(): array
    {
        return [
            'not_found'             => [ErrorType::NOT_FOUND,             404],
            'unauthorized'          => [ErrorType::UNAUTHORIZED,          401],
            'forbidden'             => [ErrorType::FORBIDDEN,             403],
            'conflict'              => [ErrorType::CONFLICT,              409],
            'invalid_argument'      => [ErrorType::INVALID_ARGUMENT,      400],
            'internal_server_error' => [ErrorType::INTERNAL_SERVER_ERROR, 500],
        ];
    }

    public function test_domain_exception_preserves_message_in_error_response(): void
    {
        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->with(
                self::callback(fn(ErrorResponse $dto) => $dto->message === 'user not found'),
                'json'
            )
            ->willReturn('{}');

        $event = $this->makeEvent('/api/any', $this->makeDomainException(ErrorType::NOT_FOUND, 'user not found'));
        ($this->listener)($event);
    }

    public function test_domain_exception_code_is_error_type_name(): void
    {
        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->with(
                self::callback(fn(ErrorResponse $dto) => $dto->code === 'NOT_FOUND'),
                'json'
            )
            ->willReturn('{}');

        $event = $this->makeEvent('/api/any', $this->makeDomainException(ErrorType::NOT_FOUND));
        ($this->listener)($event);
    }

    public function test_handles_invalid_argument_exception(): void
    {
        $event = $this->makeEvent('/api/any', new \InvalidArgumentException('bad input'));
        ($this->listener)($event);

        self::assertSame(400, $event->getResponse()->getStatusCode());
    }

    public function test_invalid_argument_exception_preserves_message(): void
    {
        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->with(
                self::callback(fn(ErrorResponse $dto) => $dto->message === 'bad input'
                    && $dto->code === 'INVALID_ARGUMENT'),
                'json'
            )
            ->willReturn('{}');

        $event = $this->makeEvent('/api/any', new \InvalidArgumentException('bad input'));
        ($this->listener)($event);
    }

    public function test_handles_http_exception(): void
    {
        $event = $this->makeEvent('/api/any', new NotFoundHttpException());
        ($this->listener)($event);

        self::assertSame(404, $event->getResponse()->getStatusCode());
    }

    public function test_http_exception_code_matches_mapped_error_type(): void
    {
        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->with(
                self::callback(fn(ErrorResponse $dto) => $dto->code === 'NOT_FOUND'),
                'json'
            )
            ->willReturn('{}');

        $event = $this->makeEvent('/api/any', new NotFoundHttpException());
        ($this->listener)($event);
    }

    public function test_http_exception_with_unmapped_status_uses_internal_server_error_code(): void
    {
        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->with(
                self::callback(fn(ErrorResponse $dto) => $dto->code === 'INTERNAL_SERVER_ERROR'),
                'json'
            )
            ->willReturn('{}');

        $event = $this->makeEvent('/api/any', new HttpException(503));
        ($this->listener)($event);

        self::assertSame(503, $event->getResponse()->getStatusCode());
    }

    public function test_generic_exception_returns_500(): void
    {
        $event = $this->makeEvent('/api/any', new \RuntimeException('internal details'));
        ($this->listener)($event);

        self::assertSame(500, $event->getResponse()->getStatusCode());
    }

    public function test_generic_exception_has_safe_message(): void
    {
        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->with(
                self::callback(fn(ErrorResponse $dto) => $dto->message !== 'internal details'
                    && $dto->code === 'INTERNAL_SERVER_ERROR'),
                'json'
            )
            ->willReturn('{}');

        $event = $this->makeEvent('/api/any', new \RuntimeException('internal details'));
        ($this->listener)($event);
    }

    public function test_response_is_json_response(): void
    {
        $event = $this->makeEvent('/api/any', new \RuntimeException());
        ($this->listener)($event);

        self::assertInstanceOf(JsonResponse::class, $event->getResponse());
    }

    private function makeEvent(string $path, \Throwable $exception): ExceptionEvent
    {
        $kernel  = $this->createMock(HttpKernelInterface::class);
        $request = Request::create($path);

        return new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
    }

    private function makeDomainException(ErrorType $type, string $message = 'test message'): DomainExceptionInterface
    {
        return new class($message, $type) extends \DomainException implements DomainExceptionInterface {
            public function __construct(string $msg, private readonly ErrorType $errorType)
            {
                parent::__construct($msg);
            }

            public function type(): ErrorType
            {
                return $this->errorType;
            }
        };
    }
}
