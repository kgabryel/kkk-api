<?php

namespace App\Tests\Functional;

use App\Controller\TimersController;
use App\Dto\Entity\Timer;
use App\Entity\Timer as TimerEntity;
use App\Entity\User;
use App\Repository\TimerRepository;
use App\Tests\DataProvider\ControllerDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\EntityTestDataDto;
use App\Tests\Helper\TestCase\BaseFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Large]
#[CoversClass(TimersController::class)]
#[CoversClass(Timer::class)]
#[CoversClass(TimerEntity::class)]
#[CoversClass(TimerRepository::class)]
class TimersControllerTest extends BaseFunctionalTestCase
{
    private TimerRepository $timerRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->timerRepository = self::getContainer()->get(TimerRepository::class);
    }

    #[Test]
    #[TestDox('Usuwa encję (Timer) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserTimerWhenAvailable(): void
    {
        // Arrange
        $timer = EntityFactory::createTimer($this->user->getEmail());
        $timerId = $timer->getId();

        // Act
        $this->sendAuthorizedRequest('DELETE', sprintf('/api/timers/%s', $timerId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->timerRepository->find($timerId));
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może usunąć encji (Timer)')]
    public function itDeniesAccessToDeleteWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('DELETE', '/api/timers/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może pobrać listy encji (Timer)')]
    public function itDeniesAccessToIndexWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('GET', '/api/timers');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może utworzyć encji (Timer)')]
    public function itDeniesAccessToStoreWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('POST', '/api/timers');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może zaktulizować encji (Timer)')]
    public function itDeniesAccessToUpdateWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('PUT', '/api/timers/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nie usuwa encji (Timer) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    #[DataProviderExternal(ControllerDataProvider::class, 'unavailableTimersCases')]
    public function itRejectsDeleteTimerWhenUnavailable(array $items): void
    {
        // Arrange
        $timerId = $this->createEntitiesToCheckAccess($items);

        // Act
        $this->sendAuthorizedRequest('DELETE', sprintf('/api/timers/%s', $timerId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utowrzenia nowej encji (Timer) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnStore(): void
    {
        // Act
        $this->sendAuthorizedRequest('POST', '/api/timers', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie aktualizacji encji (Timer) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnUpdate(): void
    {
        // Arrange
        $timer = EntityFactory::createTimer($this->user->getEmail());

        // Act
        $this->sendAuthorizedRequest('PUT', sprintf('/api/timers/%s', $timer->getId()), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Nie aktualizuje encji (Timer) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    #[DataProviderExternal(ControllerDataProvider::class, 'unavailableTimersCases')]
    public function itRejectsUpdateWhenTimerUnavailable(array $items): void
    {
        // Arrange
        $timerId = $this->createEntitiesToCheckAccess($items);

        // Act
        $this->sendAuthorizedRequest('PUT', sprintf('/api/timers/%s', $timerId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Zwraca 200 i listę encji (Timer) należących do bieżącego użytkownika')]
    #[DataProviderExternal(ControllerDataProvider::class, 'timerIndexData')]
    public function itReturnsOnlyUserTimers(array $timers): void
    {
        // Prepare expected
        $expectedResponseData = $this->prepareExpectedIndexResponseData(
            $timers,
            static function (EntityTestDataDto $timer): TimerEntity {
                $data = $timer->getEntityData();
                if ($timer->getParameter('recipe') === true) {
                    $data['recipe'] = EntityFactory::createRecipe($timer->getUserEmail());
                }

                return EntityFactory::createTimer($timer->getUserEmail(), $data);
            },
            static fn (TimerEntity $timer): array => [
                'id' => $timer->getId(),
                'name' => $timer->getName(),
                'time' => $timer->getTime(),
            ],
            static function (EntityTestDataDto $entityData, User $user): bool {
                return $entityData->getUserEmail() === $user->getEmail()
                && ($entityData->getParameter('recipe') !== true);
            },
        );

        // Act
        $this->sendAuthorizedRequest('GET', '/api/timers', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseSame($expectedResponseData, true);
    }

    #[Test]
    #[TestDox('Tworzy nową encję (Timer), zwraca jej dane i 201, gdy dane są poprawne')]
    public function itStoresTimerWhenValid(): void
    {
        // Act
        $this->sendAuthorizedJsonRequest(
            'POST',
            '/api/timers',
            ['name' => 'name', 'time' => 100],
            $this->token,
        );

        // Prepare expected
        $createdTimer = $this->getLastCreatedTimer();
        $timerData = [
            'id' => $createdTimer->getId(),
            'name' => $createdTimer->getName(),
            'time' => $createdTimer->getTime(),
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonResponseEquals($timerData);
    }

    #[Test]
    #[TestDox('Aktualizuje encję (Timer), zwraca jej dane i 200, gdy dane są poprawne')]
    public function itUpdatesTimerWhenValid(): void
    {
        // Arrange
        $timer = EntityFactory::createTimer($this->user->getEmail());

        // Act
        $this->sendAuthorizedJsonRequest(
            'PUT',
            sprintf('/api/timers/%s', $timer->getId()),
            ['name' => 'name', 'time' => 100],
            $this->token,
        );

        // Prepare expected
        $timerData = [
            'id' => $timer->getId(),
            'name' => 'name',
            'time' => 100,
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseEquals($timerData);
    }

    private function createEntitiesToCheckAccess(array $items): int
    {
        $timerToFind = null;
        foreach ($items as $item) {
            if (isset($item['recipe']) && $item['recipe']) {
                $item['recipe'] = EntityFactory::createRecipe($item['email']);
            }
            $timerEmail = $item['email'];
            $toFind = $item['toFind'] ?? false;
            unset($item['email'], $item['toFind']);
            $createdIngredient = EntityFactory::createTimer($timerEmail, $item);
            if (!$toFind) {
                continue;
            }
            $timerToFind = $createdIngredient;
        }

        return $timerToFind?->getId() ?? 999999;
    }

    private function getLastCreatedTimer(): TimerEntity
    {
        return $this->timerRepository->findOneBy([], ['id' => 'DESC']);
    }
}
