<?php

namespace App\Tests\Unit\Controller;

use App\Controller\TimersController;
use App\Dto\Entity\List\TimerList;
use App\Dto\Entity\Timer;
use App\Entity\Timer as TimerEntity;
use App\Factory\DtoFactoryDispatcher;
use App\Factory\Entity\TimerFactory;
use App\Repository\TimerRepository;
use App\Repository\UserRepository;
use App\Response\TimerResponse;
use App\Service\Entity\TimerService;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\TimerValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(TimersController::class)]
class TimersControllerTest extends BaseTestCase
{
    private TimersController $controller;
    private TimerEntity $timer;
    private TimerValidation $timerValidation;

    protected function setUp(): void
    {
        $this->timer = EntityFactory::getSimpleTimer();
        $this->timerValidation = $this->createStub(TimerValidation::class);
        $dtoFactoryDispatcher = $this->getMock(
            DtoFactoryDispatcher::class,
            new AllowedMethod('getMany', new TimerList()),
            new AllowedMethod('get', $this->createStub(Timer::class)),
        );
        $userRepository = $this->createStub(UserRepository::class);
        $this->controller = $this->getMockBuilder(TimersController::class)
            ->setConstructorArgs([$dtoFactoryDispatcher, $userRepository])
            ->onlyMethods(['getUser'])
            ->getMock();
        $this->controller->method('getUser')->willReturn(EntityFactory::getSimpleUser());
    }

    #[Test]
    #[TestDox('Usuwa encję (Timer) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserTimerWhenAvailable(): void
    {
        // Arrange
        $timerService = $this->getMock(
            TimerService::class,
            new AllowedMethod('find', $this->timer, $this->once(), [1]),
            new AllowedVoidMethod('remove', $this->once(), [$this->timer]),
        );

        // Act
        $response = $this->controller->destroy(1, $timerService);

        // Assert
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie usuwa encji (Timer) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    public function itRejectsDeleteTimerWhenUnavailable(): void
    {
        // Arrange
        $timerService = $this->getMock(
            TimerService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->destroy(1, $timerService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utowrzenia nowej encji (Timer) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnStore(): void
    {
        // Arrange
        $timerFactory = $this->getMock(
            TimerFactory::class,
            new AllowedMethod('create', null, $this->once(), [$this->timerValidation]),
        );

        // Act
        $response = $this->controller->store($timerFactory, $this->timerValidation);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie aktualizacji encji (Timer) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnUpdate(): void
    {
        // Arrange
        $timerService = $this->getMock(
            TimerService::class,
            new AllowedMethod('find', $this->timer, $this->once(), [1]),
            new AllowedMethod('update', false, $this->once(), [$this->timer, $this->timerValidation]),
        );

        // Act
        $response = $this->controller->update(1, $this->timerValidation, $timerService);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie aktualizuje encji (Timer) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    public function itRejectsUpdateWhenTimerUnavailable(): void
    {
        // Arrange
        $timerService = $this->getMock(
            TimerService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->update(1, $this->timerValidation, $timerService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 200 i listę encji (Timer) należących do bieżącego użytkownika')]
    public function itReturnsOnlyUserTimers(): void
    {
        // Arrange
        $timerRepository = $this->getMock(
            TimerRepository::class,
            new AllowedMethod('findForUser', [], $this->once()),
        );

        // Act
        $response = $this->controller->index($timerRepository);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Tworzy nową encję (Timer), zwraca jej dane i 201, gdy dane są poprawne')]
    public function itStoresTimerWhenValid(): void
    {
        // Arrange
        $timerFactory = $this->getMock(
            TimerFactory::class,
            new AllowedMethod('create', $this->timer, $this->once(), [$this->timerValidation]),
        );

        // Act
        $response = $this->controller->store($timerFactory, $this->timerValidation);

        // Assert
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertInstanceOf(TimerResponse::class, $response);
    }

    #[Test]
    #[TestDox('Aktualizuje encję (Timer), zwraca jej dane i 200, gdy dane są poprawne')]
    public function itUpdatesTimerWhenValid(): void
    {
        // Arrange
        $timerService = $this->getMock(
            TimerService::class,
            new AllowedMethod('find', $this->timer, $this->once(), [1]),
            new AllowedMethod('update', true, $this->once(), [$this->timer, $this->timerValidation]),
        );

        // Act
        $response = $this->controller->update(1, $this->timerValidation, $timerService);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(TimerResponse::class, $response);
    }
}
