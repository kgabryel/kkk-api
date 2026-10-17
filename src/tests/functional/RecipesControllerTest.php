<?php

namespace App\Tests\Functional;

use App\Config\PhotoType;
use App\Controller\RecipesController;
use App\Dto\Entity\FullRecipe;
use App\Dto\Entity\FullRecipePosition;
use App\Dto\Entity\FullRecipePositionsGroup;
use App\Dto\Entity\Recipe;
use App\Dto\Entity\RecipePosition;
use App\Dto\Entity\RecipePositionsGroup;
use App\Entity\Photo;
use App\Entity\Recipe as RecipeEntity;
use App\Entity\RecipePosition as RecipePositionEntity;
use App\Entity\RecipePositionGroup as RecipePositionGroupEntity;
use App\Entity\Tag;
use App\Factory\Dto\FullRecipeFactory;
use App\Factory\Dto\FullRecipePositionFactory;
use App\Factory\Dto\FullRecipePositionsGroupFactory;
use App\Factory\Dto\RecipeFactory;
use App\Factory\Dto\RecipePositionFactory;
use App\Factory\Dto\RecipePositionsGroupFactory;
use App\Repository\PhotoRepository;
use App\Repository\RecipeRepository;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ControllerDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\EntityTestDataDto;
use App\Tests\Helper\Recipe\RecipeTestResponseBuilder;
use App\Tests\Helper\TestCase\BaseFunctionalTestCase;
use App\Tests\TestData\RecipeTestData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Large]
#[CoversClass(RecipesController::class)]
#[CoversClass(FullRecipe::class)]
#[CoversClass(FullRecipePosition::class)]
#[CoversClass(FullRecipePositionsGroup::class)]
#[CoversClass(FullRecipeFactory::class)]
#[CoversClass(FullRecipePositionFactory::class)]
#[CoversClass(FullRecipePositionsGroupFactory::class)]
#[CoversClass(RecipeFactory::class)]
#[CoversClass(RecipePositionFactory::class)]
#[CoversClass(RecipePositionsGroupFactory::class)]
#[CoversClass(RecipeEntity::class)]
#[CoversClass(RecipePositionEntity::class)]
#[CoversClass(RecipePositionGroupEntity::class)]
#[CoversClass(Recipe::class)]
#[CoversClass(RecipePosition::class)]
#[CoversClass(RecipePositionsGroup::class)]
#[CoversClass(RecipeRepository::class)]
class RecipesControllerTest extends BaseFunctionalTestCase
{
    private RecipeRepository $recipeRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recipeRepository = self::getContainer()->get(RecipeRepository::class);
    }

    #[Test]
    #[TestDox('Usuwa encję (Recipe) i zwraca 204, gdy szukana encja istnieje i należy do bieżącego użytkownika')]
    public function itDeletesUserRecipeWhenAvailable(): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe($this->user->getEmail());
        $recipeId = $recipe->getId();
        $photo = self::createPhoto($this->user->getEmail(), ['recipe' => $recipe]);
        $photoId = $photo->getId();
        $fileName = $photo->getFilename();

        // Act
        $this->sendAuthorizedRequest('DELETE', sprintf('/api/recipes/%s', $recipeId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull($this->recipeRepository->find($recipeId));
        $this->assertNull(self::getContainer()->get(PhotoRepository::class)->find($photoId));
        self::assertFileDoesNotExist(self::getPhotoPath(PhotoType::ORIGINAL, $fileName));
        self::assertFileDoesNotExist(self::getPhotoPath(PhotoType::MEDIUM, $fileName));
        self::assertFileDoesNotExist(self::getPhotoPath(PhotoType::SMALL, $fileName));
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może usunąć encji (Recipe)')]
    public function itDeniesAccessToDeleteWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('DELETE', '/api/recipes/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może pobrać listy encji (Recipe)')]
    public function itDeniesAccessToIndexWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('GET', '/api/recipes');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może zmodyfikować encji (Recipe)')]
    public function itDeniesAccessToModifyWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('PATCH', '/api/recipes/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może utworzyć encji (Recipe)')]
    public function itDeniesAccessToStoreWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('POST', '/api/recipes');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Nieautoryzowany użytkownik nie może zaktulizować encji (Recipe)')]
    public function itDeniesAccessToUpdateWhenUnauthenticated(): void
    {
        // Act
        $this->client->request('PUT', '/api/recipes/1');

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    #[TestDox('Modyfikuje encję (Recipe), zwraca jej dane i 200, gdy dane są poprawne')]
    #[DataProviderExternal(ControllerDataProvider::class, 'modifyRecipeValidValues')]
    public function itModifiesRecipeWhenValid(?bool $favourite, ?bool $toDo): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe(
            $this->user->getEmail(),
            ['name' => 'name', 'description' => null, 'url' => null, 'public' => false, 'portions' => 3],
        );

        // Act
        $this->sendAuthorizedJsonRequest(
            'PATCH',
            sprintf('/api/recipes/%s', $recipe->getId()),
            ['favourite' => $favourite, 'toDo' => $toDo],
            $this->token,
        );

        //Prepare expected
        $recipeData = [
            'description' => null,
            'favourite' => $favourite ?? $recipe->isFavourite(),
            'groups' => [],
            'id' => $recipe->getId(),
            'name' => 'name',
            'photos' => [],
            'portions' => 3,
            'public' => false,
            'publicId' => $recipe->getPublicId(),
            'tags' => [],
            'timers' => [],
            'toDo' => $toDo ?? $recipe->isToDo(),
            'url' => null,
        ];

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseEquals($recipeData);
    }

    #[Test]
    #[TestDox('Nie usuwa encji (Recipe) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsDeleteRecipeWhenUnavailable(array $items): void
    {
        // Arrange
        $recipeId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): RecipeEntity => EntityFactory::createRecipe($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest('DELETE', sprintf('/api/recipes/%s', $recipeId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Odrzuca żądanie utowrzenia nowej encji (Recipe) i zwraca 400, gdy dane są niepoprawne')]
    public function itRejectsInvalidDataOnStore(): void
    {
        // Act
        $this->sendAuthorizedRequest('POST', '/api/recipes', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    #[Test]
    #[TestDox('Nie modyfikuje encji (Recipe) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsModifyWhenRecipeUnavailable(array $items): void
    {
        // Arrange
        $recipeId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): RecipeEntity => EntityFactory::createRecipe($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest('PATCH', sprintf('/api/recipes/%s', $recipeId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Nie aktualizuje encji (Recipe) i zwraca 404, szukana encja nie jest dostępna dla bieżącego użytkownika')]
    #[DataProviderExternal(CommonDataProvider::class, 'provideDataWithMissingEntity')]
    public function itRejectsUpdateWhenRecipeUnavailable(array $items): void
    {
        // Arrange
        $recipeId = $this->createEntitiesForAccessTest(
            $items,
            static fn (string $email, array $data): RecipeEntity => EntityFactory::createRecipe($email, $data),
        );

        // Act
        $this->sendAuthorizedRequest('PUT', sprintf('/api/recipes/%s', $recipeId), $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Zwraca 404, gdy publiczny przepis o danym publicId nie istnieje')]
    public function itReturnsNotFoundWhenPublicRecipeMissing(): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe(EntityFactory::USER_EMAIL, ['public' => false]);

        // Act
        $this->client->request('GET', sprintf('/api/public/recipes/%s', $recipe->getPublicId()));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    #[TestDox('Zwraca 200 i listę encji (Recipe) należących do bieżącego użytkownika')]
    #[DataProviderExternal(ControllerDataProvider::class, 'recipeIndexData')]
    public function itReturnsOnlyUserRecipes(array $recipes): void
    {
        // Arrange
        $recipeTestBuilder = new RecipeTestResponseBuilder();
        $expectedResponseData = [];
        foreach ($recipes as $recipeData) {
            $recipe = $recipeTestBuilder->build($recipeData);
            if ($recipeData->getUserEmail() !== $this->user->getEmail()) {
                continue;
            }
            array_unshift($expectedResponseData, $recipe->asFullResponse());
        }

        // Act
        $this->sendAuthorizedRequest('GET', '/api/recipes', $this->token);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseSame($expectedResponseData);
    }

    #[Test]
    #[TestDox('Zwraca publiczny przepis')]
    #[DataProviderExternal(ControllerDataProvider::class, 'publicRecipeData')]
    public function itReturnsPublicRecipe(EntityTestDataDto $recipeData): void
    {
        // Arrange
        $recipeTestBuilder = new RecipeTestResponseBuilder();
        $responseData = $recipeTestBuilder->build($recipeData);

        // Act
        $this->client->request('GET', sprintf('/api/public/recipes/%s', $responseData->getPublicId()));

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseSame($responseData->asPublicResponse());
    }

    #[Test]
    #[TestDox('Tworzy nową encję (Recipe), zwraca jej dane i 201, gdy dane są poprawne')]
    public function itStoresRecipeWhenValid(): void
    {
        // Arrange
        $tags = $this->createUserTags('TAG1', 'TAG2');
        $ingredient1 = EntityFactory::createIngredient($this->user->getEmail(), ['name' => 'ingredient1']);
        $recipe1 = EntityFactory::createRecipe($this->user->getEmail(), ['name' => 'recipe1']);

        // Act
        $this->sendAuthorizedJsonRequest(
            'POST',
            '/api/recipes',
            RecipeTestData::validStoreRequest($ingredient1, $recipe1),
            $this->token,
        );

        // Prepare expected
        $createdRecipe = $this->getLastCreatedRecipe();
        $recipeData = RecipeTestData::expectedStoreResponse($ingredient1, $recipe1, $createdRecipe, $tags);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJsonResponseSame($recipeData);
    }

    #[Test]
    #[TestDox('Aktualizuje przepis gdy dane są poprawne')]
    public function itUpdatesRecipeWhenValid(): void
    {
        // Arrange
        $recipe = EntityFactory::createRecipe(
            $this->user->getEmail(),
            [
                'description' => null,
                'favourite' => false,
                'name' => 'name',
                'portions' => 3,
                'public' => false,
                'toDo' => true,
                'url' => null,
            ],
        );
        $tags = $this->createUserTags('TAG1', 'TAG2');
        $ingredient1 = EntityFactory::createIngredient($this->user->getEmail(), ['name' => 'ingredient1']);
        $recipe1 = EntityFactory::createRecipe($this->user->getEmail(), ['name' => 'recipe1']);
        $photos = $this->createUserPhotos(
            ['recipe' => $recipe, 'height' => 100, 'type' => 'image/jpg', 'width' => 100],
            ['recipe' => $recipe, 'height' => 500, 'type' => 'image/png', 'width' => 100],
        );

        // Act
        $this->sendAuthorizedJsonRequest(
            'PUT',
            sprintf('/api/recipes/%s', $recipe->getId()),
            RecipeTestData::validUpdateRequest($ingredient1, $recipe1),
            $this->token,
        );

        // Prepare expected
        $recipeData = RecipeTestData::expectedUpdateResponse(
            $ingredient1,
            $recipe1,
            $recipe,
            $photos,
            $tags,
        );

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonResponseSame($recipeData);
    }

    private function createUserPhotos(array ...$photosData): array
    {
        return array_map(
            fn (array $photoData): Photo => self::createPhoto($this->user->getEmail(), $photoData),
            $photosData,
        );
    }

    private function createUserTags(string ...$names): array
    {
        return array_map(
            fn (string $name): Tag => EntityFactory::createTag($this->user->getEmail(), ['name' => $name]),
            $names,
        );
    }

    private function getLastCreatedRecipe(): RecipeEntity
    {
        return $this->recipeRepository->findOneBy([], ['id' => 'DESC']);
    }
}
