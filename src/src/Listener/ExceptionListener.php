<?php

namespace App\Listener;

use App\Utils\MonologDatabaseHandler;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    private MonologDatabaseHandler $databaseHandler;

    public function __construct(MonologDatabaseHandler $databaseHandler)
    {
        $this->databaseHandler = $databaseHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        $this->databaseHandler->write(
            $e->getMessage(),
            json_encode($e->getTrace(), JSON_THROW_ON_ERROR),
            json_encode([
                'code' => $e->getCode(),
                'exception' => FlattenException::createFromThrowable($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], JSON_THROW_ON_ERROR),
        );
    }
}
