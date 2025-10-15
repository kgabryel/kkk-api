<?php

namespace App\Tests\Unit\Factory\Entity;

use App\Entity\ApiKey;
use App\Entity\User;
use App\Factory\Entity\ApiKeyFactory;
use App\Service\UserService;
use App\Tests\Helper\AllowedMethod\AllowedCallbackMethod;
use App\Tests\Helper\AllowedMethod\AllowedExceptionMethod;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(ApiKeyFactory::class)]
#[CoversClass(ApiKey::class)]
class ApiKeyFactoryTest extends BaseTestCase
{
    private User $user;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->user = EntityFactory::getSimpleUser();
        $this->userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user),
        );
    }

    #[Test]
    #[TestDox('Tworzy encję (ApiKey) i przypisuje go do użytkownika')]
    public function itCreatesValidApiKeyWhenValid(): void
    {
        // Arrange
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->once()),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $factory = new ApiKeyFactory($entityManager, $this->userService);

        // Act
        $key = $factory->generate();

        // Assert
        $this->assertInstanceOf(ApiKey::class, $key);
        $this->assertFalse($key->isActive());
        $this->assertSame($this->user, $key->getUser());
    }

    #[Test]
    #[TestDox('Zwraca null po 10 nieudanych próbach zapisu')]
    public function itReturnsNullAfterTenFailures(): void
    {
        // Arrange
        $exception = $this->getMock(UniqueConstraintViolationException::class);
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->exactly(10)),
            new AllowedExceptionMethod('flush', $this->exactly(10), $exception),
        );
        $factory = new ApiKeyFactory($entityManager, $this->userService);

        // Act
        $key = $factory->generate();

        // Assert
        $this->assertNull($key);
    }

    #[Test]
    #[TestDox('Ignoruje kilka błędów, tworzy encję (ApiKey) i przypisuje go do użytkownika')]
    public function itSucceedsAfterFewTransientErrors(): void
    {
        // Arrange
        $i = 0;
        $exception = $this->createStub(UniqueConstraintViolationException::class);
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->atLeast(3)),
            new AllowedCallbackMethod(
                'flush',
                function () use (&$i, $exception): void {
                    if ($i++ < 3) {
                        throw $exception;
                    }
                },
                $this->atLeast(3),
            ),
        );
        $factory = new ApiKeyFactory($entityManager, $this->userService);

        // Act
        $key = $factory->generate();

        // Assert
        $this->assertInstanceOf(ApiKey::class, $key);
        $this->assertFalse($key->isActive());
        $this->assertSame($this->user, $key->getUser());
    }
}
