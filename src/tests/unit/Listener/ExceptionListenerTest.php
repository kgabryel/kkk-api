<?php

namespace App\Tests\Unit\Listener;

use App\Listener\ExceptionListener;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Utils\MonologDatabaseHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[Small]
#[CoversClass(ExceptionListener::class)]
class ExceptionListenerTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Zwraca poprawne mapowanie subskrybowanych zdarzeń')]
    public function itReturnsCorrectSubscribedEventsArray(): void
    {
        // Arrange
        $expected = [KernelEvents::EXCEPTION => 'onKernelException'];

        // Act
        $result = ExceptionListener::getSubscribedEvents();

        // Assert
        $this->assertSame($expected, $result);
    }

    #[Test]
    #[TestDox('Zapisuje wyjątek przy zdarzeniu kernel.exception')]
    public function itWritesException(): void
    {
        // Arrange
        $handler = $this->getMock(
            MonologDatabaseHandler::class,
            new AllowedVoidMethod('write', $this->once()),
        );
        $event = $this->getMock(
            ExceptionEvent::class,
            new AllowedMethod('getThrowable', overrideValue: false),
        );
        $listener = new ExceptionListener($handler);

        // Act
        $listener->onKernelException($event);
    }
}
