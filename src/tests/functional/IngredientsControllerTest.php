<?php

namespace App\Tests\Functional;

use App\Controller\IngredientsController;
use App\Dto\Entity\Ingredient;
use App\Dto\Entity\OzaSupply;
use App\Entity\Ingredient as IngredientEntity;
use App\Repository\IngredientRepository;
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
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Large]
#[CoversClass(IngredientsController::class)]
#[CoversClass(Ingredient::class)]
#[CoversClass(IngredientEntity::class)]
#[CoversClass(IngredientRepository::class)]
#[CoversClass(OzaSupply::class)]
class IngredientsControllerTest extends BaseFunctionalTestCase
{
    private IngredientRepository $ingredientRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ingredientRepository = self::getContainer()->get(IngredientRepository::class);
    }

    #[Test]
    #[TestDox('Usuwa encję (Ingredient) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserIngredientWhenAvailable(): void
    {
        // Arrange
        $ingredient = EntityFactory::createIngredient($this->user->getEmail());
        $ingredientId = $ingredient->getId();

        // Act
        $this->sendAuthorizedRequest(
            'DELETE',
            sprintf('/api/ingredients/%s', $ingredientId),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->ingredientRepository->find($ingredientId));
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może usunąć encji (Ingredient)')]
    public function itDeniesAccessToDeleteWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('DELETE', '/api/ingredients/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może pobrać listy encji (Ingredient)')]
    public function itDeniesAccessToIndexWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('GET', '/api/ingredients');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może zmodyfikować encji (Ingredient)')]
    public function itDeniesAccessToModifyWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('PATCH', '/api/ingredients/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może pobrać listy OzaSupply')]
    public function itDeniesAccessToOzaSupplyWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('GET', '/api/ingredients/oza-supplies');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może utworzyć encji (Ingredient)')]
    public function itDeniesAccessToStoreWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('POST', '/api/ingredients');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nie usuwa encji (Ingredient) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsDeleteIngredientWhenUnavailable(array $items): void
    {
        // Arrange
        $ingredientId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): IngredientEntity => EntityFactory::createIngredient($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest(
            'DELETE',
            sprintf('/api/ingredients/%s', $ingredientId),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie modyfikacji encji (Ingredient) i zwraca 400, gdy dane są niepoprawne')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsInvalidDataOnModify(array $items): void
    {
        // Arrange
        $ingredientId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): IngredientEntity => EntityFactory::createIngredient($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest(
            'PATCH',
            sprintf('/api/ingredients/%s', $ingredientId),
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utowrzenia nowej encji (Ingredient) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnStore(): void
    {
        // Act
        $this->sendAuthorizedRequest('POST', '/api/ingredients', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie aktualizacji encji (Ingredient) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnUpdate(): void
    {
        // Arrange
        $ingredient = EntityFactory::createIngredient($this->user->getEmail());

        // Act
        $this->sendAuthorizedJsonRequest(
            'PATCH',
            sprintf('/api/ingredients/%s', $ingredient->getId()),
            ['name' => ''],
            $this->token,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie usunięcia powiązania OzaSupply z Ingredient i zwraca 403')]
    public function itRejectsOzaSupplyWhenOzaKeyIsMissing(): void
    {
        // Arrange
        $this->initSettings(['ozaKey' => null]);

        // Act
        $this->sendAuthorizedRequest('GET', '/api/ingredients/oza-supplies', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    #[TestDox('Zwraca 200 i listę encji (Ingredient) należących do bieżącego użytkownika')]
    #[DataProviderExternal(ControllerDataProvider::class, 'ingredientIndexData')]
    public function itReturnsOnlyUserIngredients(array $ingredients): void
    {
        // Arrange
        $expectedResponseData = $this->prepareExpectedIndexResponseData(
            $ingredients,
            static fn (EntityTestDataDto $ingredient): IngredientEntity => EntityFactory::createIngredient(
                $ingredient->getUserEmail(),
                $ingredient->getEntityData(),
            ),
            static fn (IngredientEntity $ingredient): array => [
                'available' => $ingredient->isAvailable(),
                'id' => $ingredient->getId(),
                'name' => $ingredient->getName(),
                'ozaId' => $ingredient->getOzaId(),
            ],
        );

        // Act
        $this->sendAuthorizedRequest('GET', '/api/ingredients', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseSame($expectedResponseData, true);
    }

    #[Test]
    #[TestDox('Zwraca zapasy pobrane z API OZA')]
    #[DataProviderExternal(ControllerDataProvider::class, 'suppliesResponseData')]
    public function itReturnsOzaSupplies(string $responseData, array $expectedData): void
    {
        // Arrange
        $mockResponse = new MockResponse(
            $responseData,
            [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'application/json'],
            ],
        );
        $mockClient = new MockHttpClient($mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $mockClient);
        $this->initSettings(['ozaKey' => 'oza-key']);

        // Act
        $this->sendAuthorizedRequest('GET', '/api/ingredients/oza-supplies', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseSame($expectedData, true);
    }

    #[Test]
    #[TestDox('Tworzy nową encję (Ingredient), zwraca jej dane i 201, gdy dane są poprawne')]
    #[DataProviderExternal(ControllerDataProvider::class, 'createIngredientValidValues')]
    public function itStoresIngredientWhenValid(string $name, bool $available, ?int $ozaId): void
    {
        // Act
        $this->sendAuthorizedJsonRequest(
            'POST',
            '/api/ingredients',
            ['name' => $name, 'available' => $available, 'ozaId' => $ozaId],
            $this->token,
        );

        // Prepare expected
        $createdIngredient = $this->getLastCreatedIngredient();
        $ingredientData = [
            'available' => $available,
            'id' => $createdIngredient->getId(),
            'name' => $name,
            'ozaId' => $ozaId,
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonResponseEquals($ingredientData);
    }

    #[Test]
    #[TestDox('Aktualizuje encję (Ingredient), zwraca jej dane i 200, gdy dane są poprawne')]
    #[DataProviderExternal(ControllerDataProvider::class, 'updateIngredientValidValues')]
    public function itUpdatesIngredientWhenValid(array $updateData): void
    {
        // Arrange
        $ingredient = EntityFactory::createIngredient($this->user->getEmail(), ['name' => 'name']);

        // Act
        $this->sendAuthorizedJsonRequest(
            'PATCH',
            sprintf('/api/ingredients/%s', $ingredient->getId()),
            $updateData,
            $this->token,
        );

        // Prepare expected
        $ingredientData = $this->prepareExpectedUpdateResponseData($ingredient, $updateData);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseEquals($ingredientData);
    }

    private function getLastCreatedIngredient(): IngredientEntity
    {
        return $this->ingredientRepository->findOneBy([], ['id' => 'DESC']);
    }

    private function initSettings(array $data): void
    {
        EntityFactory::createSettings($this->user->getEmail(), $data);
    }

    private function prepareExpectedUpdateResponseData(IngredientEntity $ingredient, array $updateData): array
    {
        if (!isset($updateData['ozaId'])) {
            $ozaId = $ingredient->getOzaId();
        } elseif ($updateData['ozaId'] === 0) {
            $ozaId = null;
        } else {
            $ozaId = $updateData['ozaId'];
        }
        if (($updateData['available'] ?? false)) {
            $ozaId = null;
        }

        return [
            'available' => $updateData['available'] ?? $ingredient->isAvailable(),
            'id' => $ingredient->getId(),
            'name' => $updateData['name'] ?? $ingredient->getName(),
            'ozaId' => $ozaId,
        ];
    }
}
