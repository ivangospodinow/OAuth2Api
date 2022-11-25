<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $event->allowCustomResponseCode();

        $exception = $event->getThrowable();
        $response = new JsonResponse([
            'error' => $exception->getMessage(),
            'code' => method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500,
        ], 200);

        $event->setResponse($response);
    }
}
