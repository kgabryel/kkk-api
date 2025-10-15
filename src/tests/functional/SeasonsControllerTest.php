<?php

namespace App\Tests\Functional;

use App\Controller\SeasonsController;
use App\Dto\Entity\Season;
use App\Entity\Season as SeasonEntity;
use App\Repository\SeasonRepository;
use App\Tests\DataProvider\CommonDataProvider;
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
#[CoversClass(SeasonsController::class)]
#[CoversClass(Season::class)]
#[CoversClass(SeasonEntity::class)]
#[CoversClass(SeasonRepository::class)]
class SeasonsControllerTest extends BaseFunctionalTestCase
{
    private SeasonRepository $seasonRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seasonRepository = self::getContainer()->get(SeasonRepository::class);
    }

    #[Test]
    #[TestDox('Usuwa encję (Season) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserSeasonWhenAvailable(): void
    {
        // Arrange
        $season = EntityFactory::createSeason($this->user->getEmail());
        $seasonId = $season->getId();

        // Act
        $this->sendAuthorizedRequest('DELETE', sprintf('/api/seasons/%s', $seasonId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->seasonRepository->find($seasonId));
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może usunąć encji (Season)')]
    public function itDeniesAccessToDeleteWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('DELETE', '/api/seasons/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może pobrać listy encji (Season)')]
    public function itDeniesAccessToIndexWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('GET', '/api/seasons');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może zmodyfikować encji (Season)')]
    public function itDeniesAccessToModifyWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('PATCH', '/api/seasons/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może utworzyć encji (Season)')]
    public function itDeniesAccessToStoreWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('POST', '/api/seasons');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Modyfikuje encję (Season), zwraca jej dane i 200, gdy dane są poprawne')]
    public function itModifiesSeasonWhenValid(): void
    {
        // Arrange
        $season = EntityFactory::createSeason($this->user->getEmail(), ['start' => 1, 'stop' => 12]);

        // Act
        $this->sendAuthorizedJsonRequest(
            'PATCH',
            sprintf('/api/seasons/%s', $season->getId()),
            ['start' => 2, 'stop' => 6],
            $this->token,
        );

        // Prepare expected
        $seasonData = [
            'id' => $season->getId(),
            'ingredientId' => $season->getIngredient()->getId(),
            'start' => 2,
            'stop' => 6,
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseEquals($seasonData);
    }

    #[Test]
    #[TestDox('Nie usuwa encji (Season) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsDeleteSeasonWhenUnavailable(array $items): void
    {
        // Arrange
        $seasonId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): SeasonEntity => EntityFactory::createSeason($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest('DELETE', sprintf('/api/seasons/%s', $seasonId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie modyfikacji encji (Season) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnModify(): void
    {
        // Arrange
        $season = EntityFactory::createSeason($this->user->getEmail());

        // Act
        $this->sendAuthorizedRequest(
            'PATCH',
            sprintf('/api/seasons/%s', $season->getId()),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utowrzenia nowej encji (Season) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnStore(): void
    {
        // Act
        $this->sendAuthorizedRequest('POST', '/api/seasons', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Nie modyfikuje encji (Season) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsModifyWhenSeasonUnavailable(array $items): void
    {
        // Arrange
        $seasonId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): SeasonEntity => EntityFactory::createSeason($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest('PATCH', sprintf('/api/seasons/%s', $seasonId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Zwraca 200 i listę encji (Season) należących do bieżącego użytkownika')]
    #[DataProviderExternal(ControllerDataProvider::class, 'seasonIndexData')]
    public function itReturnsOnlyUserSeasons(array $seasons): void
    {
        // Prepare expected
        $expectedResponseData = $this->prepareExpectedIndexResponseData(
            $seasons,
            static fn (EntityTestDataDto $season): SeasonEntity => EntityFactory::createSeason(
                $season->getUserEmail(),
                $season->getEntityData(),
            ),
            static fn (SeasonEntity $season): array => [
                'id' => $season->getId(),
                'ingredientId' => $season->getIngredient()->getId(),
                'start' => $season->getStart(),
                'stop' => $season->getStop(),
            ],
        );

        // Act
        $this->sendAuthorizedRequest('GET', '/api/seasons', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseSame($expectedResponseData, true);
    }

    #[Test]
    #[TestDox('Tworzy nową encję (Season), zwraca jej dane i 201, gdy dane są poprawne')]
    public function itStoresSeasonWhenValid(): void
    {
        // Arrange
        $ingredient = EntityFactory::createIngredient($this->user->getEmail());

        // Act
        $this->sendAuthorizedJsonRequest(
            'POST',
            '/api/seasons',
            ['start' => 1, 'stop' => 2, 'ingredient' => $ingredient->getId()],
            $this->token,
        );

        // Prepare expected
        $createdSeason = $this->getLastCreatedSeason();
        $seasonData = [
            'id' => $createdSeason->getId(),
            'ingredientId' => $createdSeason->getIngredient()->getId(),
            'start' => $createdSeason->getStart(),
            'stop' => $createdSeason->getStop(),
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonResponseEquals($seasonData);
    }

    private function getLastCreatedSeason(): SeasonEntity
    {
        return $this->seasonRepository->findOneBy([], ['id' => 'DESC']);
    }
}
