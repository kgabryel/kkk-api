<?php

namespace App\Tests\Unit\Factory\Entity;

use App\Dto\Request\Timer as TimerRequest;
use App\Entity\Timer;
use App\Entity\User;
use App\Factory\Entity\TimerFactory;
use App\Service\UserService;
use App\Tests\DataProvider\EntityFactoryDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\TimerValidation;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(TimerFactory::class)]
#[CoversClass(Timer::class)]
#[CoversClass(TimerRequest::class)]
class TimerFactoryTest extends BaseTestCase
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
    #[TestDox('Tworzy encję (Timer), gdy walidacja przeszła pomyślnie')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'timerDataValues')]
    public function itCreatesTimerOnValidInput(string $timerName, int $time): void
    {
        // Arrange
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod(
                'persist',
                $this->once(),
                [$this->callback($this->timerMatcher($timerName, $time))],
            ),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $factory = new TimerFactory($entityManager, $this->userService);
        $timerModel = new TimerRequest($timerName, $time);
        $timerValidation = $this->getMock(
            TimerValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $timerModel, $this->once()),
        );

        // Act
        $timer = $factory->create($timerValidation);

        // Assert
        $this->assertInstanceOf(Timer::class, $timer);
    }

    #[Test]
    #[TestDox('Odrzuca dane i przerywa tworzenie, gdy walidacja się nie powiodła')]
    public function itRejectsWhenValidationFails(): void
    {
        // Arrange
        $factory = new TimerFactory($this->getMock(EntityManagerInterface::class), $this->userService);
        $timerValidation = $this->getMock(
            TimerValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $timer = $factory->create($timerValidation);

        // Assert
        $this->assertNull($timer);
    }

    private function timerMatcher(?string $timerName, int $time): callable
    {
        $user = $this->user;

        return static function ($timer) use ($user, $timerName, $time): bool {
            return $timer instanceof Timer
                && $timer->getName() === $timerName
                && $timer->getTime() === $time
                && $timer->getUser() === $user;
        };
    }
}
