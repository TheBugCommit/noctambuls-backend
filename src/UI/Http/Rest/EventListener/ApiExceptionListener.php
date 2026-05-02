<?php
declare(strict_types=1);

namespace App\UI\Http\Rest\EventListener;

use App\Shared\Domain\Exception\DomainExceptionInterface;
use App\Shared\Domain\Exception\ErrorType;
use App\UI\Http\Rest\Response\ErrorResponse;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Serializer\SerializerInterface;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: -1)]
final class ApiExceptionListener
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        #[Autowire('%kernel.debug%')] private readonly bool $debug
    ){}
    private const array ERROR_TYPE_TO_STATUS = [
        ErrorType::NOT_FOUND->name             => Response::HTTP_NOT_FOUND,
        ErrorType::UNAUTHORIZED->name          => Response::HTTP_UNAUTHORIZED,
        ErrorType::FORBIDDEN->name             => Response::HTTP_FORBIDDEN,
        ErrorType::CONFLICT->name              => Response::HTTP_CONFLICT,
        ErrorType::INVALID_ARGUMENT->name      => Response::HTTP_BAD_REQUEST,
        ErrorType::INTERNAL_SERVER_ERROR->name => Response::HTTP_INTERNAL_SERVER_ERROR,
    ];

    public function __invoke(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $event->getThrowable();

        /*if($this->debug) {
            dd($exception);
        }*/

        $status = self::ERROR_TYPE_TO_STATUS[ErrorType::INTERNAL_SERVER_ERROR->name];
        $code = ErrorType::INTERNAL_SERVER_ERROR->name;
        $message = 'An unexpected server error occurred.';

        if ($exception instanceof DomainExceptionInterface) {
            $status = self::ERROR_TYPE_TO_STATUS[$exception->type()->name];
            $code = $exception->type()->name;
            $message = $exception->getMessage();
        }
        elseif ($exception instanceof \InvalidArgumentException) {
            $status = self::ERROR_TYPE_TO_STATUS[ErrorType::INVALID_ARGUMENT->name];
            $code = ErrorType::INVALID_ARGUMENT->name;
            $message = $exception->getMessage();
        }

        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $errorType  = array_search($status, self::ERROR_TYPE_TO_STATUS, strict: true);
            $code       = $errorType !== false ? $errorType : ErrorType::INTERNAL_SERVER_ERROR->name;
            $message    = Response::$statusTexts[$status] ?? 'HTTP Error';
        }

        $errorDto = new ErrorResponse($message, $code);

        $json = $this->serializer->serialize($errorDto, 'json');
        $event->setResponse(new JsonResponse($json, $status, [], true));
    }
}
