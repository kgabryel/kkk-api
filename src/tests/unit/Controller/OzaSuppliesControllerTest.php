<?php

namespace App\Tests\Unit\Controller;

use App\Controller\OzaSuppliesController;
use App\Entity\Ingredient;
use App\Factory\DtoFactoryDispatcher;
use App\Repository\UserRepository;
use App\Service\Entity\IngredientService;
use App\Tests\DataProvider\OzaSupplyDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(OzaSuppliesController::class)]
class OzaSuppliesControllerTest extends BaseTestCase
{
    private OzaSuppliesController $controller;
    private Ingredient $ingredient;

    protected function setUp(): void
    {
        $this->ingredient = EntityFactory::getSimpleIngredient();
    }

    #[Test]
    #[TestDox('Odłącza zapas OZA od encji (Ingredient) i zwraca 204 gdy przesłano poprawny access token')]
    public function itDisconnectIngredientFromOza(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientService::class,
                new AllowedMethod('findByOzaId', $this->ingredient, $this->once()),
                new AllowedVoidMethod('disconnectFromOZA', $this->once(), parameters: [$this->ingredient]),
            ),
        );

        // Act
        $response = $this->controller->destroy(1);

        // Assert
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 404 przy próbie modyfikacji niedostępnego zapasu OZA')]
    public function itRejectsModifyAvailableValue(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientService::class,
                new AllowedMethod('findByOzaId', null, $this->once(), [1]),
            ),
        );
        $request = $this->getMock(Request::class);

        // Act
        $response = $this->controller->modify(1, $request);

        // Assert
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 404 przy próbie usunięcia niedostępnego zapasu OZA')]
    public function itRejectsUpdateWhenSupplyUnavailable(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientService::class,
                new AllowedMethod('findByOzaId', null, $this->once(), [1]),
            ),
        );

        // Act
        $response = $this->controller->destroy(1);

        // Assert
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Aktualizuje dostępność zapasu OZA i zwraca 204, gdy został znaleziony')]
    #[DataProviderExternal(OzaSupplyDataProvider::class, 'statusesValues')]
    public function itUpdatesAvailabilityWhenSupplyFound(mixed $requestValue, bool $expectedAvailable): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                IngredientService::class,
                new AllowedMethod('findByOzaId', $this->ingredient, $this->once()),
                new AllowedVoidMethod('updateAvailable', $this->once(), [$this->ingredient, $expectedAvailable]),
            ),
        );
        $request = $this->getMock(
            Request::class,
            new AllowedMethod('get', $requestValue, $this->once(), ['available']),
        );

        // Act
        $response = $this->controller->modify(1, $request);

        // Assert
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    private function init(IngredientService $ingredientService): void
    {
        $dtoFactoryDispatcher = $this->createStub(DtoFactoryDispatcher::class);
        $userRepository = $this->createStub(UserRepository::class);
        $this->controller = new OzaSuppliesController($dtoFactoryDispatcher, $userRepository, $ingredientService);
    }
}
