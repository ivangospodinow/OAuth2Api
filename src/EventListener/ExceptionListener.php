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
            'code' => $exception->getStatusCode(),
        ], 200);

        $event->setResponse($response);
    }
}
