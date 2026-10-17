<?php

namespace App\Tests\Unit\Service\Entity;

use App\Dto\Request\List\OrderList;
use App\Dto\Request\Order;
use App\Dto\Request\Recipe;
use App\Dto\Request\RecipeFlags;
use App\Dto\Request\RecipePosition;
use App\Dto\Request\RecipePositionsGroup;
use App\Entity\Photo;
use App\Entity\Recipe as RecipeEntity;
use App\Entity\RecipePositionGroup;
use App\Entity\Timer;
use App\Entity\User;
use App\Repository\RecipeRepository;
use App\Service\Entity\PhotoService;
use App\Service\Entity\RecipeService;
use App\Service\RecipeFillService;
use App\Service\UserService;
use App\Tests\DataProvider\FindEntityDataProvider;
use App\Tests\DataProvider\RecipeDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedCallbackMethod;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\Recipe\RecipeValidation;
use App\Validation\RecipeFlagsValidation;
use App\Validation\ReorderPhotosValidation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(RecipeService::class)]
#[CoversClass(Order::class)]
#[CoversClass(Recipe::class)]
#[CoversClass(RecipeFlags::class)]
#[CoversClass(RecipePosition::class)]
#[CoversClass(RecipePositionsGroup::class)]
class RecipeServiceTest extends BaseTestCase
{
    private RecipeEntity $recipe;
    private RecipeService $recipeService;
    private User $user;

    protected function setUp(): void
    {
        $this->recipe = EntityFactory::getSimpleRecipe();
        $this->user = EntityFactory::getSimpleUser();
    }

    #[Test]
    #[TestDox('Zwraca encję (Recipe) użytkownika znalezioną w repozytorium')]
    #[DataProviderExternal(FindEntityDataProvider::class, 'recipeValues')]
    public function itFindsRecipe(int $id, ?RecipeEntity $recipe): void
    {
        // Arrange
        $this->init(
            recipeRepository: $this->getMock(
                RecipeRepository::class,
                new AllowedMethod('findById', $recipe, $this->once(), [$id, $this->user]),
            ),
        );

        // Act
        $result = $this->recipeService->find($id);

        // Assert
        $this->assertSame($recipe, $result);
    }

    #[Test]
    #[TestDox('Modyfikuje Recipe, gdy formularz jest poprawny')]
    #[DataProviderExternal(RecipeDataProvider::class, 'flagValues')]
    public function itModifiesRecipeWhenFormIsValid(?bool $favourite, ?bool $toDo): void
    {
        // Arrange
        $allowedMethods = [];
        if ($favourite !== null) {
            $allowedMethods[] = new AllowedVoidMethod('setFavourite', $this->once(), [$favourite]);
        }
        if ($toDo !== null) {
            $allowedMethods[] = new AllowedVoidMethod('setToDo', $this->once(), [$toDo]);
        }
        $recipe = $this->getMock(RecipeEntity::class, ...$allowedMethods);
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->once(), [$recipe]),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $this->init($entityManager);
        $recipeFlagValidation = $this->getMock(
            RecipeFlagsValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', new RecipeFlags($favourite, $toDo), $this->once()),
        );

        // Act
        $result = $this->recipeService->modify($recipe, $recipeFlagValidation);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox('Odrzuca modyfikację encji (Ingredient), gdy formularz jest niepoprawny')]
    public function itRejectsInvalidFormOnModify(): void
    {
        // Arrange
        $recipeClone = clone $this->recipe;
        $this->init();
        $recipeFlagValidation = $this->getMock(
            RecipeFlagsValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $result = $this->recipeService->modify($this->recipe, $recipeFlagValidation);

        // Assert
        $this->assertFalse($result);
        $this->assertEquals($recipeClone, $this->recipe);
    }

    #[Test]
    #[TestDox('Odrzuca zmianę kolejności zdjęć, gdy formularz jest niepoprawny')]
    public function itRejectsInvalidFormOnReorderPhotos(): void
    {
        // Arrange
        $this->init();
        $reorderPhotoValidation = $this->getMock(
            ReorderPhotosValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $result = $this->recipeService->reorderPhotos($this->recipe, $reorderPhotoValidation);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox('Odrzuca aktualizację encji (Recipe), gdy formularz jest niepoprawny')]
    public function itRejectsInvalidFormOnUpdate(): void
    {
        // Arrange
        $recipeClone = clone $this->recipe;
        $this->init();
        $recipeValidation = $this->getMock(
            RecipeValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $result = $this->recipeService->update($this->recipe, $recipeValidation);

        // Assert
        $this->assertFalse($result);
        $this->assertEquals($recipeClone, $this->recipe);
    }

    #[Test]
    #[TestDox('Usuwa encję (Photo) i wykonuje usunięcie powiązanych zdjęć z bazy danych')]
    #[DataProviderExternal(RecipeDataProvider::class, 'photoValues')]
    public function itRemovesRecipe(array $photos): void
    {
        // Arrange
        foreach ($photos as $photo) {
            $this->recipe->addPhoto($photo);
        }
        $removedPhoto = [];
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('remove', $this->once(), [$this->recipe]),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $this->init($entityManager);
        $photoService = $this->getMock(
            PhotoService::class,
            new AllowedCallbackMethod(
                'remove',
                function (Photo $photo) use (&$removedPhoto): void {
                    $removedPhoto[] = $photo;
                },
                $this->exactly(count($photos)),
            ),
        );

        // Act
        $this->recipeService->remove($this->recipe, $photoService);

        // Assert
        $this->assertSame($photos, $removedPhoto);
    }

    #[Test]
    #[TestDox('Wykonuje reorderPhotos, gdy formularz jest poprawny')]
    #[DataProviderExternal(RecipeDataProvider::class, 'reorderPhotosValues')]
    public function itReorderPhotosWhenFormIsValid(array $recipePhotos, OrderList $request): void
    {
        // Arrange
        $updated = [];
        foreach ($recipePhotos as $index => $recipePhoto) {
            $recipePhoto->setPhotoOrder($index + 999);
            $this->recipe->addPhoto($recipePhoto);
        }
        $reorderPhotoValidation = $this->getMock(
            ReorderPhotosValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $request, $this->once()),
        );
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('flush', $this->exactly(count($recipePhotos))),
            new AllowedCallbackMethod(
                'persist',
                function (Photo $photo) use (&$updated): void {
                    $updated[$photo->getId()] = $photo;
                },
                $this->exactly(count($recipePhotos)),
            ),
        );
        $this->init($entityManager);

        // Act
        $result = $this->recipeService->reorderPhotos($this->recipe, $reorderPhotoValidation);

        // Assert
        $this->assertTrue($result);
        foreach ($request->get() as $order) {
            $this->assertSame($recipePhotos[$order->getId()]->getPhotoOrder(), $order->getIndex());
        }
        sort($recipePhotos);
        sort($updated);
        $this->assertSame($recipePhotos, $updated);
    }

    #[Test]
    #[TestDox('Aktualizuje encję (Recipe), gdy formularz jest poprawny')]
    #[DataProviderExternal(RecipeDataProvider::class, 'positionsGroupsAndTimersValues')]
    public function itUpdatesRecipeWhenFormIsValid(ArrayCollection $positionsGroups, ArrayCollection $timers): void
    {
        // Arrange
        $toRemove = array_merge($positionsGroups->toArray(), $timers->toArray());
        $removed = [];
        $recipe = $this->getMock(
            RecipeEntity::class,
            new AllowedVoidMethod('clearTags', $this->once()),
            new AllowedMethod('getRecipePositionGroups', $positionsGroups, $this->once()),
            new AllowedMethod('getTimers', $timers, $this->once()),
        );
        $recipeModel = $this->getMock(Recipe::class);
        $recipeValidation = $this->getMock(
            RecipeValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $recipeModel, $this->once()),
        );
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod(
                'flush',
                $this->exactly($positionsGroups->count() + $timers->count() + 2),
            ),
            new AllowedVoidMethod('persist', $this->once(), [$recipe]),
            new AllowedCallbackMethod(
                'remove',
                function (RecipePositionGroup|Timer $entity) use (&$removed): void {
                    $removed[] = $entity;
                },
                $this->exactly($positionsGroups->count() + $timers->count()),
            ),
        );
        $recipeFillService = $this->getMock(
            RecipeFillService::class,
            new AllowedVoidMethod('fillRecipeBasicData', $this->once(), [$recipe, $recipeModel]),
            new AllowedVoidMethod('assignTags', $this->once(), [$recipe, $recipeModel]),
            new AllowedVoidMethod('assignPositions', $this->once(), [$recipe, $recipeModel]),
            new AllowedVoidMethod('assignTimers', $this->once(), [$recipe, $recipeModel]),
        );
        $this->init($entityManager, recipeFillService: $recipeFillService);

        // Act
        $result = $this->recipeService->update($recipe, $recipeValidation);

        // Assert
        $this->assertTrue($result);
        $this->assertSame($toRemove, $removed);
    }

    private function init(
        ?EntityManagerInterface $entityManager = null,
        ?RecipeRepository $recipeRepository = null,
        ?RecipeFillService $recipeFillService = null,
    ): void {
        $userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user),
        );
        $this->recipeService = new RecipeService(
            $entityManager ?? $this->getMock(EntityManagerInterface::class),
            $userService,
            $recipeRepository ?? $this->getMock(RecipeRepository::class),
            $recipeFillService ?? $this->getMock(RecipeFillService::class),
        );
    }
}
