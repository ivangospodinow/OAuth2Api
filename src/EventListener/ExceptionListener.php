<?php
namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $event->allowCustomResponseCode();

        $exception = $event->getThrowable();
        $defaultCode = $exception instanceof MissingTokenException ? 401 : 500;
        $response = new JsonResponse([
            'errors' => [
                [
                    'property' => 'app',
                    'message' => $exception->getMessage(),
                ],
            ],
            'code' => method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : $defaultCode,
        ], 200);

        $event->setResponse($response);
    }

    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        // rethrow to go to onKernelException
        throw $event->getException();
    }
}
