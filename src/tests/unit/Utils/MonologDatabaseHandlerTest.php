<?php

namespace App\Tests\Unit\Utils;

use App\Entity\Log;
use App\Tests\DataProvider\LogDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Utils\MonologDatabaseHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(MonologDatabaseHandler::class)]
#[CoversClass(Log::class)]
class MonologDatabaseHandlerTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Zapisuje log do bazy z przekazanymi danymi')]
    #[DataProviderExternal(LogDataProvider::class, 'logDataValues')]
    public function itPersistsLogEntryWithCorrectValues(string $logMessage, string $logContext, string $logExtra): void
    {
        // Arrange
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedMethod(
                'persist',
                invokedCount: $this->once(),
                parameters: [
                    $this->callback($this->logEntryMatcher($logMessage, $logContext, $logExtra)),
                ],
                overrideValue: false,
            ),
            new AllowedMethod('flush', invokedCount: $this->once(), overrideValue: false),
        );
        $managerRegistry = $this->getMock(
            ManagerRegistry::class,
            new AllowedVoidMethod('resetManager', $this->once()),
        );
        $handler = new MonologDatabaseHandler($entityManager, $managerRegistry);

        // Act
        $handler->write($logMessage, $logContext, $logExtra);
    }

    private function logEntryMatcher(string $logMessage, string $logContext, string $logExtra): callable
    {
        return static function ($logEntry) use ($logMessage, $logContext, $logExtra): bool {
            return $logEntry instanceof Log
                && $logEntry->getMessage() === $logMessage
                && $logEntry->getContext() === $logContext
                && $logEntry->getExtra() === $logExtra;
        };
    }
}
