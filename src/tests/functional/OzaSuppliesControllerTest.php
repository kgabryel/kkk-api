<?php

namespace App\Tests\Functional;

use App\Controller\OzaSuppliesController;
use App\Entity\ApiKey;
use App\Entity\Ingredient;
use App\Security\ApiAuthenticator;
use App\Tests\DataProvider\ControllerDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Large]
#[CoversClass(OzaSuppliesController::class)]
#[CoversClass(ApiAuthenticator::class)]
class OzaSuppliesControllerTest extends BaseFunctionalTestCase
{
    #[Test]
    #[TestDox('Odrzuca usunięcie zapasu OZA przy nieprawidłowym access tokenie')]
    public function itDeniesAccessToDeleteWithInvalidToken(): void
    {
        // Act
        $this->client->request(
            'DELETE',
            '/api/oza/supplies/1',
            server: ['HTTP_X_AUTH_TOKEN' => 'auth token'],
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Odrzuca modyfikację zapasu OZA przy nieprawidłowym access tokenie')]
    public function itDeniesAccessToModifyWithInvalidToken(): void
    {
        // Act
        $this->client->request(
            'PATCH',
            '/api/oza/supplies/1',
            server: ['HTTP_X_AUTH_TOKEN' => 'auth token'],
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Odrzuca modyfikację zapasu OZA bez access tokenu')]
    public function itDeniesAccessToModifyWithoutToken(): void
    {
        // Act
        $this->client->request('PATCH', '/api/oza/supplies/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może odłączyć zapas OZA od encji (Ingredient)')]
    public function itDeniesAccessToSupplyDisconnectWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('DELETE', '/api/oza/supplies/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Odłącza zapas OZA od encji (Ingredient) i zwraca 204 gdy przesłano poprawny access token')]
    public function itDisconnectIngredientFromOza(): void
    {
        // Arrange
        $apiKey = $this->initApiKey(['active' => true]);
        $ingredient = $this->initIngredient(['ozaId' => 100]);

        // Act
        $this->client->request(
            'DELETE',
            '/api/oza/supplies/100',
            server: ['HTTP_X_AUTH_TOKEN' => $apiKey->getKey()],
        );

        // Refresh entities from DB
        $this->entityManager->refresh($ingredient);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull($ingredient->getOzaId());
    }

    #[Test]
    #[TestDox('Aktualizuje dostępność zapasu OZA i zwraca 204')]
    public function itModifyAvailableValue(): void
    {
        // Arrange
        $apiKey = $this->initApiKey(['active' => true]);
        $ingredient = $this->initIngredient(['ozaId' => 100, 'available' => false]);

        // Act
        $this->client->request(
            'PATCH',
            '/api/oza/supplies/100?available=true',
            server: ['HTTP_X_AUTH_TOKEN' => $apiKey->getKey()],
        );

        // Refresh entities from DB
        $this->entityManager->refresh($ingredient);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertTrue($ingredient->isAvailable());
    }

    #[Test]
    #[TestDox('Zwraca 404 przy próbie modyfikacji niedostępnego zapasu OZA')]
    #[DataProviderExternal(ControllerDataProvider::class, 'unavailableIngredientsCases')]
    public function itRejectsModifyAvailableValue(array $items): void
    {
        // Arrange
        $apiKey = $this->initApiKey(['active' => true]);
        $ozaId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): Ingredient => EntityFactory::createIngredient($email, $data),
        );

        // Act
        $this->client->request(
            'PATCH',
            sprintf('/api/oza/supplies/%s', $ozaId),
            server: ['HTTP_X_AUTH_TOKEN' => $apiKey->getKey()],
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Zwraca 404 przy próbie usunięcia niedostępnego zapasu OZA')]
    #[DataProviderExternal(ControllerDataProvider::class, 'unavailableIngredientsCases')]
    public function itRejectsUpdateWhenSupplyUnavailable(array $items): void
    {
        // Arrange
        $apiKey = $this->initApiKey(['active' => true]);
        $ozaId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): Ingredient => EntityFactory::createIngredient($email, $data),
        );

        // Act
        $this->client->request(
            'DELETE',
            sprintf('/api/oza/supplies/%s', $ozaId),
            server: ['HTTP_X_AUTH_TOKEN' => $apiKey->getKey()],
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function initApiKey(array $data = []): ApiKey
    {
        return EntityFactory::createApiKey($this->user->getEmail(), $data);
    }

    private function initIngredient(array $data = []): Ingredient
    {
        return EntityFactory::createIngredient($this->user->getEmail(), $data);
    }
}
