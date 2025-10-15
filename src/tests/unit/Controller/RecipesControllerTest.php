<?php

namespace App\Tests\Unit\Controller;

use App\Controller\RecipesController;
use App\Dto\Entity\List\RecipeList;
use App\Dto\Entity\Recipe;
use App\Entity\Recipe as RecipeEntity;
use App\Factory\DtoFactoryDispatcher;
use App\Factory\Entity\RecipeFactory;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use App\Response\FullRecipeResponse;
use App\Response\RecipeResponse;
use App\Service\Entity\PhotoService;
use App\Service\Entity\RecipeService;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\Recipe\RecipeValidation;
use App\Validation\RecipeFlagsValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(RecipesController::class)]
class RecipesControllerTest extends BaseTestCase
{
    private RecipesController $controller;
    private PhotoService $photoService;
    private RecipeEntity $recipe;
    private RecipeFlagsValidation $recipeFlagsValidation;
    private RecipeValidation $recipeValidation;

    protected function setUp(): void
    {
        $this->recipe = EntityFactory::getSimpleRecipe();
        $this->photoService = $this->createStub(PhotoService::class);
        $this->recipeValidation = $this->createStub(RecipeValidation::class);
        $this->recipeFlagsValidation = $this->createStub(RecipeFlagsValidation::class);
        $dtoFactoryDispatcher = $this->getMock(
            DtoFactoryDispatcher::class,
            new AllowedMethod('getMany', new RecipeList()),
            new AllowedMethod('get', $this->createStub(Recipe::class)),
        );
        $userRepository = $this->createStub(UserRepository::class);
        $this->controller = $this->getMockBuilder(RecipesController::class)
            ->setConstructorArgs([$dtoFactoryDispatcher, $userRepository])
            ->onlyMethods(['getUser'])
            ->getMock();
        $this->controller->method('getUser')->willReturn(EntityFactory::getSimpleUser());
    }

    #[Test]
    #[TestDox('Usuwa encję (Recipe) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserRecipeWhenAvailable(): void
    {
        // Arrange
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', $this->recipe, $this->once(), [1]),
            new AllowedVoidMethod('remove', $this->once(), [$this->recipe]),
        );

        // Act
        $response = $this->controller->destroy(1, $recipeService, $this->photoService);

        // Assert
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie usuwa encji (Recipe) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    public function itRejectsDeleteRecipeWhenUnavailable(): void
    {
        // Arrange
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->destroy(1, $recipeService, $this->photoService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie modyfikacji encji (Recipe) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnModify(): void
    {
        // Arrange
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', $this->recipe, $this->once(), [1]),
            new AllowedMethod(
                'modify',
                false,
                $this->once(),
                [$this->recipe,
                    $this->recipeFlagsValidation],
            ),
        );

        // Act
        $response = $this->controller->modify(1, $this->recipeFlagsValidation, $recipeService);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utowrzenia nowej encji (Recipe) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnStore(): void
    {
        // Arrange
        $recipeFactory = $this->getMock(
            RecipeFactory::class,
            new AllowedMethod('create', null, $this->once(), [$this->recipeValidation]),
        );

        // Act
        $response = $this->controller->store($recipeFactory, $this->recipeValidation);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Odrzuca żądanie aktualizacji encji (Recipe) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnUpdate(): void
    {
        // Arrange
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', $this->recipe, $this->once(), [1]),
            new AllowedMethod(
                'update',
                false,
                $this->once(),
                [$this->recipe, $this->recipeValidation],
            ),
        );

        // Act
        $response = $this->controller->update(1, $this->recipeValidation, $recipeService);

        // Assert
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie modyfikuje encji (Recipe) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    public function itRejectsModifyWhenRecipeUnavailable(): void
    {
        // Arrange
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->modify(1, $this->recipeFlagsValidation, $recipeService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Nie aktualizuje encji (Recipe) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    public function itRejectsUpdateWhenRecipeUnavailable(): void
    {
        // Arrange
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', null, $this->once(), [1]),
        );

        // Act
        $response = $this->controller->update(1, $this->recipeValidation, $recipeService);

        // Assert
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 404, gdy publiczny przepis o danym publicId nie istnieje')]
    public function itReturnsNotFoundWhenPublicRecipeMissing(): void
    {
        // Arrange
        $repository = $this->getMock(
            RecipeRepository::class,
            new AllowedMethod(
                'findOneBy',
                null,
                $this->once(),
                [
                    [
                        'public' => true,
                        'publicId' => 'public-id',
                    ],
                ],
            ),
        );

        // Act
        $response = $this->controller->public('public-id', $repository);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 200 i listę encji (Recipe) należących do bieżącego użytkownika')]
    public function itReturnsOnlyUserRecipes(): void
    {
        // Arrange
        $recipeRepository = $this->getMock(
            RecipeRepository::class,
            new AllowedMethod('findForUser', [], $this->once()),
        );

        // Act
        $response = $this->controller->index($recipeRepository);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca publiczny przepis')]
    public function itReturnsPublicRecipe(): void
    {
        // Arrange
        $repository = $this->getMock(
            RecipeRepository::class,
            new AllowedMethod(
                'findOneBy',
                $this->recipe,
                $this->once(),
                [
                    [
                        'public' => true,
                        'publicId' => 'public-id',
                    ],
                ],
            ),
        );

        // Act
        $response = $this->controller->public('public-id', $repository);

        // Assert
        $this->assertInstanceOf(FullRecipeResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Tworzy nową encję (Recipe), zwraca jej dane i 201, gdy dane są poprawne')]
    public function itStoresRecipeWhenValid(): void
    {
        // Arrange
        $recipeFactory = $this->getMock(
            RecipeFactory::class,
            new AllowedMethod('create', $this->recipe, $this->once(), [$this->recipeValidation]),
        );

        // Act
        $response = $this->controller->store($recipeFactory, $this->recipeValidation);

        // Assert
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertInstanceOf(RecipeResponse::class, $response);
    }

    #[Test]
    #[TestDox('Aktualizuje flagi przepisu, zwraca dane encji (Recipe) i 200, gdy dane są poprawne')]
    public function itUpdatesFlagsWhenValid(): void
    {
        // Arrange
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', $this->recipe, $this->once(), [1]),
            new AllowedMethod(
                'modify',
                true,
                $this->once(),
                [$this->recipe, $this->recipeFlagsValidation],
            ),
        );

        // Act
        $response = $this->controller->modify(1, $this->recipeFlagsValidation, $recipeService);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(RecipeResponse::class, $response);
    }

    #[Test]
    #[TestDox('Aktualizuje encję (Recipe), zwraca jej dane i 200, gdy dane są poprawne')]
    public function itUpdatesRecipeWhenValid(): void
    {
        // Arrange
        $recipeService = $this->getMock(
            RecipeService::class,
            new AllowedMethod('find', $this->recipe, $this->once(), [1]),
            new AllowedMethod('update', true, $this->once(), [$this->recipe, $this->recipeValidation]),
        );

        // Act
        $response = $this->controller->update(1, $this->recipeValidation, $recipeService);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(RecipeResponse::class, $response);
    }
}
