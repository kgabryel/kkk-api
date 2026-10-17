<?php

namespace App\Tests\Unit\Controller;

use App\Controller\IngredientsController;
use App\Dto\Entity\Ingredient;
use App\Dto\Entity\List\IngredientList;
use App\Dto\Entity\List\OzaSupplyList;
use App\Dto\Entity\OzaSupply;
use App\Entity\Ingredient as IngredientEntity;
use App\Factory\DtoFactoryDispatcher;
use App\Factory\Entity\IngredientFactory;
use App\Repository\IngredientRepository;
use App\Repository\UserRepository;
use App\Response\IngredientResponse;
use App\Response\OzaSupplyListResponse;
use App\Service\Entity\IngredientService;
use App\Service\OzaSuppliesService;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\CreateIngredientValidation;
use App\Validation\EditIngredientValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(IngredientsController::class)]
class IngredientsControllerTest extends BaseTestCase
{
    private IngredientsController $controller;
    private CreateIngredientValidation $createIngredientValidation;
    private EditIngredientValidation $editIngredientValidation;
    private IngredientEntity $ingredient;

    protected function setUp(): void
    {
        $this->ingredient = EntityFactory::getSimpleIngredient();
        $this->editIngredientValidation = $this->createStub(EditIngredientValidation::class);
        $this->createIngredientValidation = $this->createStub(CreateIngredientValidation::class);
        $dtoFactoryDispatcher = $this->getMock(
            DtoFactoryDispatcher::class,
            new AllowedMethod('getMany', new IngredientList()),
            new AllowedMethod('get', $this->createStub(Ingredient::class)),
        );
        $userRepository = $this->createStub(UserRepository::class);
        $this->controller = $this->getMockBuilder(IngredientsController::class)
            ->setConstructorArgs([$dtoFactoryDispatcher, $userRepository])
            ->onlyMethods(['getUser'])
            ->getMock();
        $this->controller->method('getUser')->willReturn(EntityFactory::getSimpleUser());
    }

    #[Test]
    #[TestDox(
        'Usuwa encję (Ingredient) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika',
    )]
    public function itDeletesUserIngredientWhenAvailable(): void
    {
        // Arrange
        $ingredientService = $this->getMock(
            IngredientService::class,
            new AllowedMethod('find', $this->ingredient, $this->once(), [1]),
            new AllowedVoidMethod('remove', $this->once(), [$this->ingredient]),
        );

        // Act
        $response = $this->controller->destroy(1, $ingredientService);

        // Assert
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie usuwa encji (Ingredient) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    public function itRejectsDeleteIngredientWhenUnavailable(): void
    {
        // Arrange
        $ingredientService = $this->getMock(
            IngredientService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->destroy(1, $ingredientService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utowrzenia nowej encji (Ingredient) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnStore(): void
    {
        // Arrange
        $ingredientFactory = $this->getMock(
            IngredientFactory::class,
            new AllowedMethod('create', null, $this->once(), [$this->createIngredientValidation]),
        );

        // Act
        $response = $this->controller->store($ingredientFactory, $this->createIngredientValidation);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie aktualizacji encji (Ingredient) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnUpdate(): void
    {
        // Arrange
        $ingredientService = $this->getMock(
            IngredientService::class,
            new AllowedMethod('find', $this->ingredient, $this->once(), [1]),
            new AllowedMethod(
                'update',
                false,
                $this->once(),
                [$this->ingredient, $this->editIngredientValidation],
            ),
        );

        // Act
        $response = $this->controller->modify(1, $this->editIngredientValidation, $ingredientService);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox(
        'Nie aktualizuje encji (Ingredient) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika',
    )]
    public function itRejectsUpdateWhenIngredientUnavailable(): void
    {
        // Arrange
        $ingredientService = $this->getMock(
            IngredientService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->modify(1, $this->editIngredientValidation, $ingredientService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 403, gdy nie uda się pobrać danych z OZA')]
    public function itReturnsErrorWhenDownloadFails(): void
    {
        // Arrange
        $ozaSuppliesService = $this->getMock(
            OzaSuppliesService::class,
            new AllowedMethod('downloadSupplies', false, $this->once()),
            new AllowedMethod('getErrorStatusCode', Response::HTTP_UNAUTHORIZED, $this->once()),
        );

        // Act
        $response = $this->controller->getOzaSupplies($ozaSuppliesService);

        // Assert
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca dane w formacie JSON, gdy pobieranie z OZA się powiedzie')]
    public function itReturnsJsonWithSupplies(): void
    {
        // Arrange
        $fakeSupplies = [EntityFactory::getSimpleOzaSupply(), EntityFactory::getSimpleOzaSupply(2)];
        $fakeDtoResponse = new OzaSupplyList(
            new OzaSupply(1, 'dto1', true, '1kg'),
            new OzaSupply(2, 'dto2', false, '0ml'),
        );
        $ozaSuppliesService = $this->getMock(
            OzaSuppliesService::class,
            new AllowedMethod('downloadSupplies', true, $this->once()),
            new AllowedMethod('getSupplies', $fakeSupplies, $this->once()),
        );
        $dtoFactoryDispatcher = $this->getMock(
            DtoFactoryDispatcher::class,
            new AllowedMethod(
                'getMany',
                $fakeDtoResponse,
                $this->once(),
                [OzaSupplyList::class, ...$fakeSupplies],
            ),
        );
        $userRepository = $this->createStub(UserRepository::class);
        $this->controller = $this->getMockBuilder(IngredientsController::class)
            ->setConstructorArgs([$dtoFactoryDispatcher, $userRepository])
            ->onlyMethods(['getUser'])
            ->getMock();

        // Act
        $response = $this->controller->getOzaSupplies($ozaSuppliesService);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(OzaSupplyListResponse::class, $response);
        $this->assertEquals(json_encode($fakeDtoResponse), $response->getContent());
    }

    #[Test]
    #[TestDox('Zwraca 200 i listę encji (Ingredient) należących do bieżącego użytkownika')]
    public function itReturnsOnlyUserIngredients(): void
    {
        // Arrange
        $ingredientRepository = $this->getMock(
            IngredientRepository::class,
            new AllowedMethod('findForUser', [], $this->once()),
        );

        // Act
        $response = $this->controller->index($ingredientRepository);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Tworzy nową encję (Ingredient), zwraca jej dane i 201, gdy dane są poprawne')]
    public function itStoresIngredientWhenValid(): void
    {
        // Arrange
        $ingredientFactory = $this->getMock(
            IngredientFactory::class,
            new AllowedMethod(
                'create',
                $this->ingredient,
                $this->once(),
                [$this->createIngredientValidation],
            ),
        );

        // Act
        $response = $this->controller->store($ingredientFactory, $this->createIngredientValidation);

        // Assert
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertInstanceOf(IngredientResponse::class, $response);
    }

    #[Test]
    #[TestDox('Aktualizuje encję (Ingredient), zwraca jej dane i 200, gdy dane są poprawne')]
    public function itUpdatesIngredientWhenValid(): void
    {
        // Arrange
        $ingredientService = $this->getMock(
            IngredientService::class,
            new AllowedMethod('find', $this->ingredient, $this->once(), [1]),
            new AllowedMethod(
                'update',
                true,
                $this->once(),
                [$this->ingredient, $this->editIngredientValidation],
            ),
        );

        // Act
        $response = $this->controller->modify(1, $this->editIngredientValidation, $ingredientService);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(IngredientResponse::class, $response);
    }
}
