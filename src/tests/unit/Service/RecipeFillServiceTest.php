<?php

namespace App\Tests\Unit\Service;

use App\Dto\List\Type\StringList;
use App\Dto\Request\List\RecipePositionsGroupList;
use App\Dto\Request\List\TimerList;
use App\Dto\Request\Recipe as RecipeRequest;
use App\Entity\Recipe;
use App\Entity\RecipePosition as RecipePositionEntity;
use App\Entity\RecipePositionGroup;
use App\Entity\Tag;
use App\Entity\Timer;
use App\Entity\User;
use App\Repository\TagRepository;
use App\Service\RecipeFillService;
use App\Service\UserService;
use App\Tests\DataProvider\RecipeDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedCallbackMethod;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(RecipeFillService::class)]
class RecipeFillServiceTest extends BaseTestCase
{
    private RecipeFillService $recipeFillService;
    private User $user;

    protected function setUp(): void
    {
        $this->user = EntityFactory::getSimpleUser();
    }

    #[Test]
    #[TestDox('Dodaje wszystkie tagi do przepisu, tworząc brakujące')]
    #[DataProviderExternal(RecipeDataProvider::class, 'validTagsValues')]
    public function itAddsTags(array $existingTags, array $newTags): void
    {
        // Arrange
        $entityManager = $this->createEntityManager($newTags, $this->tagMatcher($newTags));
        $tagRepository = $this->getMock(
            TagRepository::class,
            new AllowedCallbackMethod('findOneBy', $this->tagCreator($existingTags)),
        );
        $this->init($entityManager, $tagRepository);
        $recipe = $this->getMock(
            Recipe::class,
            new AllowedVoidMethod('addTag', $this->exactly(count($existingTags) + count($newTags))),
        );
        $recipeRequest = $this->prepareRecipeRequest(
            new StringList(...array_merge($existingTags, $newTags)),
            new TimerList(),
            new RecipePositionsGroupList(),
        );

        // Act
        $this->recipeFillService->assignTags($recipe, $recipeRequest);
    }

    #[Test]
    #[TestDox('Przypisuje grupy pozycji i ich encje do przepisu')]
    #[DataProviderExternal(RecipeDataProvider::class, 'validPositionsValues')]
    public function itAssignCorrectsEntities(RecipePositionsGroupList $requestDto, array $expectedGroups): void
    {
        // Arrange
        $entitiesToAssign = [];
        foreach ($expectedGroups as $group) {
            foreach ($group->getRecipePositions() as $position) {
                $entitiesToAssign[] = $position::class;
            }
            $entitiesToAssign[] = $group::class;
        }
        $entityManager = $this->createEntityManager($entitiesToAssign, $this->entityClassMatcher($entitiesToAssign));
        $this->init($entityManager);
        $recipe = EntityFactory::getSimpleRecipe();
        $recipeRequest = $this->prepareRecipeRequest(new StringList(), new TimerList(), $requestDto);

        // Act
        $this->recipeFillService->assignPositions($recipe, $recipeRequest);

        // Assert
        $this->assertSame(
            array_map(fn (RecipePositionGroup $group): array => $this->normalizeGroup($group), $expectedGroups),
            array_map(
                fn (RecipePositionGroup $group): array => $this->normalizeGroup($group),
                $recipe->getRecipePositionGroups()->toArray(),
            ),
        );
    }

    #[Test]
    #[TestDox('Dodaje timery do przepisu')]
    #[DataProviderExternal(RecipeDataProvider::class, 'validTimerValues')]
    public function itAssignTimers(array $timers): void
    {
        // Arrange
        $entityManager = $this->createEntityManager($timers, $this->timerMatcher($timers));
        $this->init($entityManager);
        $recipe = $this->getMock(
            Recipe::class,
            new AllowedVoidMethod('addTimer', $this->exactly(count($timers))),
        );
        $recipeRequest = $this->prepareRecipeRequest(
            new StringList(),
            new TimerList(...$timers),
            new RecipePositionsGroupList(),
        );

        // Act
        $this->recipeFillService->assignTimers($recipe, $recipeRequest);
    }

    #[Test]
    #[TestDox('Wypełnia podstawowe dane przepisu poprawnie')]
    #[DataProviderExternal(RecipeDataProvider::class, 'validBasicRecipeValues')]
    public function itFillRecipeBasicData(RecipeRequest $requestData): void
    {
        // Arrange
        $this->init();
        $recipe = new Recipe();

        // Act
        $this->recipeFillService->fillRecipeBasicData($recipe, $requestData);

        // Assert
        $this->assertSame($requestData->getName(), $recipe->getName());
        $this->assertSame($requestData->isFavourite(), $recipe->isFavourite());
        $this->assertSame($requestData->isToDo(), $recipe->isToDo());
        $this->assertSame($requestData->isPublic(), $recipe->isPublic());
        $this->assertSame($requestData->getDescription(), $recipe->getDescription());
        $this->assertSame($requestData->getPortions(), $recipe->getPortions());
        $this->assertSame($requestData->getUrl(), $recipe->getUrl());
    }

    private function createEntityManager(array $expectedEntities, callable $comparator): EntityManagerInterface
    {
        return $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod(
                'persist',
                $this->exactly(count($expectedEntities)),
                [$this->callback($comparator)],
            ),
        );
    }

    private function entityClassMatcher(array $entitiesToAssign): callable
    {
        $i = 0;

        return static function (object $entity) use (&$i, $entitiesToAssign): bool {
            return $entity::class === $entitiesToAssign[$i++];
        };
    }

    private function init(?EntityManagerInterface $entityManager = null, ?TagRepository $tagRepository = null): void
    {
        $userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user),
        );
        $this->recipeFillService = new RecipeFillService(
            $entityManager ?? $this->getMock(EntityManagerInterface::class),
            $tagRepository ?? $this->getMock(TagRepository::class),
            $userService,
        );
    }

    private function normalizeGroup(RecipePositionGroup $group): array
    {
        return [
            'name' => $group->getName(),
            'positions' => array_map(
                static fn (RecipePositionEntity $pos): array => [
                    'additional' => $pos->isAdditional(),
                    'amount'     => $pos->getAmount(),
                    'ingredient' => $pos->getIngredient()?->getId(),
                    'measure'    => $pos->getMeasure(),
                    'recipe'     => $pos->getRecipe()?->getId(),
                ],
                $group->getRecipePositions()->toArray(),
            ),
        ];
    }

    private function prepareRecipeRequest(
        StringList $tags,
        TimerList $timers,
        RecipePositionsGroupList $recipePositions
    ): RecipeRequest {
        return new RecipeRequest(
            'Spaghetti',
            'Pyszne włoskie danie',
            'https://example.com',
            4,
            true,
            false,
            true,
            $tags,
            $timers,
            $recipePositions,
        );
    }

    private function tagCreator(array $existingTags): callable
    {
        $user = $this->user;

        return static function (array $criteria) use ($existingTags, $user): ?Tag {
            if (!in_array($criteria['name'], $existingTags) || $criteria['user'] !== $user) {
                return null;
            }

            $tag = new Tag();
            $tag->setName($criteria['name']);
            $tag->setUser($user);

            return $tag;
        };
    }

    private function tagMatcher(array $newTags): callable
    {
        $i = 0;
        $user = $this->user;

        return static function (mixed $tag) use (&$i, $newTags, $user): bool {
            $index = $i++;

            return $tag instanceof Tag
                && $tag->getUser() === $user
                && $tag->getName() === strtoupper($newTags[$index]);
        };
    }

    private function timerMatcher(array $timers): callable
    {
        $i = 0;
        $user = $this->user;

        return static function (mixed $timer) use (&$i, $timers, $user): bool {
            $index = $i++;

            return $timer instanceof Timer
                && $timer->getUser() === $user
                && $timer->getName() === $timers[$index]->getName()
                && $timer->getTime() === $timers[$index]->getTime();
        };
    }
}
