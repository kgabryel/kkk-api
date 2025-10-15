<?php

namespace App\Tests\Unit\Service\Entity;

use App\Dto\Request\Timer;
use App\Entity\Timer as TimerEntity;
use App\Entity\User;
use App\Repository\TimerRepository;
use App\Service\Entity\TimerService;
use App\Service\UserService;
use App\Tests\DataProvider\FindEntityDataProvider;
use App\Tests\DataProvider\UpdateEntityDataProvider;
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
#[CoversClass(TimerService::class)]
#[CoversClass(Timer::class)]
class TimerServiceTest extends BaseTestCase
{
    private TimerEntity $timer;
    private TimerService $timerService;
    private User $user;

    protected function setUp(): void
    {
        $this->timer = EntityFactory::getSimpleTimer();
        $this->user = EntityFactory::getSimpleUser();
    }

    #[Test]
    #[TestDox('Zwraca encję (Timer) użytkownika znalezioną w repozytorium')]
    #[DataProviderExternal(FindEntityDataProvider::class, 'timerValues')]
    public function itFindsTimer(int $id, ?TimerEntity $timer): void
    {
        // Arrange
        $this->init(timerRepository: $this->getMock(
            TimerRepository::class,
            new AllowedMethod('findById', $timer, $this->once(), [$id, $this->user]),
        ));

        // Act
        $result = $this->timerService->find($id);

        // Assert
        $this->assertSame($timer, $result);
    }

    #[Test]
    #[TestDox('Odrzuca aktualizację encji (Timer), gdy formularz jest niepoprawny')]
    public function itRejectsInvalidForm(): void
    {
        // Arrange
        $timerClone = clone $this->timer;
        $this->init($this->getMock(EntityManagerInterface::class));
        $timerValidation = $this->getMock(
            TimerValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $result = $this->timerService->update($this->timer, $timerValidation);

        // Assert
        $this->assertFalse($result);
        $this->assertEquals($timerClone, $this->timer);
    }

    #[Test]
    #[TestDox('Usuwa encję (Timer) z bazy danych')]
    public function itRemovesTimer(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                EntityManagerInterface::class,
                new AllowedVoidMethod('remove', $this->once(), [$this->timer]),
                new AllowedVoidMethod('flush', $this->once()),
            ),
        );

        // Act
        $this->timerService->remove($this->timer);
    }

    #[Test]
    #[TestDox('Aktualizuje encję (Timer), gdy formularz jest poprawny')]
    #[DataProviderExternal(UpdateEntityDataProvider::class, 'timerValues')]
    public function itUpdatesTimerWhenFormIsValid(string $name, int $time): void
    {
        // Arrange
        $timerClone = clone $this->timer;
        $timerClone->setName($name);
        $timerClone->setTime($time);
        $this->init(
            $this->getMock(
                EntityManagerInterface::class,
                new AllowedVoidMethod('persist', $this->once(), [$this->timer]),
                new AllowedVoidMethod('flush', $this->once()),
            ),
        );
        $timerModel = new Timer($name, $time);
        $timerValidation = $this->getMock(
            TimerValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $timerModel, $this->once()),
        );

        // Act
        $result = $this->timerService->update($this->timer, $timerValidation);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals($timerClone, $this->timer);
    }

    private function init(
        ?EntityManagerInterface $entityManager = null,
        ?TimerRepository $timerRepository = null
    ): void {
        $userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user),
        );
        $this->timerService = new TimerService(
            $entityManager ?? $this->createStub(EntityManagerInterface::class),
            $userService,
            $timerRepository ?? $this->createStub(TimerRepository::class),
        );
    }
}
