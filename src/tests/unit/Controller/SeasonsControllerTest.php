<?php

namespace App\Tests\Unit\Controller;

use App\Controller\SeasonsController;
use App\Dto\Entity\List\SeasonList;
use App\Dto\Entity\Season;
use App\Entity\Season as SeasonEntity;
use App\Factory\DtoFactoryDispatcher;
use App\Factory\Entity\SeasonFactory;
use App\Repository\SeasonRepository;
use App\Repository\UserRepository;
use App\Response\SeasonResponse;
use App\Service\Entity\SeasonService;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\SeasonValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(SeasonsController::class)]
class SeasonsControllerTest extends BaseTestCase
{
    private SeasonsController $controller;
    private SeasonEntity $season;
    private SeasonValidation $seasonValidation;

    protected function setUp(): void
    {
        $this->season = EntityFactory::getSimpleSeason();
        $this->seasonValidation = $this->createStub(SeasonValidation::class);
        $dtoFactoryDispatcher = $this->getMock(
            DtoFactoryDispatcher::class,
            new AllowedMethod('getMany', new SeasonList()),
            new AllowedMethod('get', $this->createStub(Season::class)),
        );
        $userRepository = $this->createStub(UserRepository::class);
        $this->controller = $this->getMockBuilder(SeasonsController::class)
            ->setConstructorArgs([$dtoFactoryDispatcher, $userRepository])
            ->onlyMethods(['getUser'])
            ->getMock();
        $this->controller->method('getUser')->willReturn(EntityFactory::getSimpleUser());
    }

    #[Test]
    #[TestDox('Usuwa encję (Season) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserSeasonWhenAvailable(): void
    {
        // Arrange
        $seasonService = $this->getMock(
            SeasonService::class,
            new AllowedMethod('find', $this->season, $this->once(), [1]),
            new AllowedVoidMethod('remove', $this->once(), [$this->season]),
        );

        // Act
        $response = $this->controller->destroy(1, $seasonService);

        // Assert
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Modyfikuje encję (Season), zwraca jej dane i 200, gdy dane są poprawne')]
    public function itModifiesSeasonWhenValid(): void
    {
        // Arrange
        $seasonService = $this->getMock(
            SeasonService::class,
            new AllowedMethod('find', $this->season, $this->once(), [1]),
            new AllowedMethod('update', true, $this->once(), [$this->season, $this->seasonValidation]),
        );

        // Act
        $response = $this->controller->modify(1, $this->seasonValidation, $seasonService);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(SeasonResponse::class, $response);
    }

    #[Test]
    #[TestDox('Nie usuwa encji (Season) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    public function itRejectsDeleteSeasonWhenUnavailable(): void
    {
        // Arrange
        $seasonService = $this->getMock(
            SeasonService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->destroy(1, $seasonService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie modyfikacji encji (Season) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnModify(): void
    {
        // Arrange
        $seasonService = $this->getMock(
            SeasonService::class,
            new AllowedMethod('find', $this->season, $this->once(), [1]),
            new AllowedMethod(
                'update',
                false,
                $this->once(),
                [$this->season, $this->seasonValidation],
            ),
        );

        // Act
        $response = $this->controller->modify(1, $this->seasonValidation, $seasonService);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utowrzenia nowej encji (Season) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnStore(): void
    {
        // Arrange
        $seasonFactory = $this->getMock(
            SeasonFactory::class,
            new AllowedMethod('create', null, $this->once(), [$this->seasonValidation]),
        );

        // Act
        $response = $this->controller->store($seasonFactory, $this->seasonValidation);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie modyfikuje encji (Season) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    public function itRejectsModifyWhenSeasonUnavailable(): void
    {
        // Arrange
        $seasonService = $this->getMock(
            SeasonService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->modify(1, $this->seasonValidation, $seasonService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 200 i listę encji (Season) należących do bieżącego użytkownika')]
    public function itReturnsOnlyUserSeasons(): void
    {
        // Arrange
        $seasonRepository = $this->getMock(
            SeasonRepository::class,
            new AllowedMethod('findForUser', [], $this->once()),
        );

        // Act
        $response = $this->controller->index($seasonRepository);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Tworzy nową encję (Season), zwraca jej dane i 201, gdy dane są poprawne')]
    public function itStoresSeasonWhenValid(): void
    {
        // Arrange
        $seasonFactory = $this->getMock(
            SeasonFactory::class,
            new AllowedMethod('create', $this->season, $this->once(), [$this->seasonValidation]),
        );

        // Act
        $response = $this->controller->store($seasonFactory, $this->seasonValidation);

        // Assert
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertInstanceOf(SeasonResponse::class, $response);
    }
}
