<?php

namespace App\Tests\Unit\Dto\Entity;

use App\Dto\Entity\List\PhotoList;
use App\Dto\Entity\List\RecipePositionsGroupList;
use App\Dto\Entity\List\TimerList;
use App\Dto\Entity\Recipe;
use App\Dto\Helper\RecipeFlags;
use App\Dto\Helper\RecipeRelatedEntities;
use App\Dto\List\Entity\PhotoList as EntityPhotoList;
use App\Dto\List\Entity\RecipePositionGroupList;
use App\Dto\List\Entity\TagList;
use App\Dto\List\Entity\TimerList as EntityTimerList;
use App\Entity\Photo;
use App\Entity\RecipePositionGroup;
use App\Entity\Tag;
use App\Entity\Timer;
use App\Factory\DtoFactoryDispatcher;
use App\Tests\Helper\AllowedMethod\AllowedCallbackMethod;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(Recipe::class)]
class RecipeTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Tworzy DTO z użyciem dispatcherów')]
    public function itBuildsDto(): void
    {
        // Arrange
        [$tags, $photos, $timers, $groups, $recipeFlags, $relatedEntities] = $this->createRecipeDependencies(
            [1, 2],
            [1],
            [],
            [1, 2, 3],
        );
        $parsed = [];
        $dtoFactoryDispatcher = $this->getMock(
            DtoFactoryDispatcher::class,
            new AllowedCallbackMethod(
                'getMany',
                function (...$args) use (&$parsed): object {
                    $parsed[$args[0]] = $args;

                    return new $args[0]();
                },
                $this->exactly(3),
            ),
        );

        // Act
        $dto = new Recipe(
            1,
            'name',
            'description',
            null,
            2,
            'id',
            $recipeFlags,
            $relatedEntities,
            $dtoFactoryDispatcher,
        );

        // Prepare expected
        $expected = [
            PhotoList::class => [PhotoList::class, ...$photos],
            RecipePositionsGroupList::class => [RecipePositionsGroupList::class, ...$groups],
            TimerList::class => [TimerList::class, ...$timers],
        ];
        $expectedTags = array_map(static fn (Tag $tag): int => $tag->getId(), $tags);

        // Assert
        $this->assertCount(count($expected), $parsed);
        foreach ($expected as $key => $expectedValue) {
            $this->assertSame($expectedValue, $parsed[$key]);
        }
        $this->assertSame($expectedTags, $dto->jsonSerialize()['tags']);
    }

    private function createRecipeDependencies(array $tagsIds, array $photoIds, array $timerIds, array $groupIds): array
    {
        $tags = array_map(static fn (int $id): Tag => EntityFactory::getSimpleTag($id), $tagsIds);
        $photos = array_map(static fn (int $id): Photo => EntityFactory::getSimplePhoto($id), $photoIds);
        $timers = array_map(static fn (int $id): Timer => EntityFactory::getSimpleTimer($id), $timerIds);
        $groups = array_map(
            static fn (int $id): RecipePositionGroup => EntityFactory::getSimpleRecipePositionGroup($id),
            $groupIds,
        );
        $recipeFlags = $this->getMock(
            RecipeFlags::class,
            new AllowedMethod('isFavourite', invokedCount: $this->once(), overrideValue: false),
            new AllowedMethod('isToDo', invokedCount: $this->once(), overrideValue: false),
            new AllowedMethod('isPublic', invokedCount: $this->once(), overrideValue: false),
        );
        $relatedEntities = $this->getMock(
            RecipeRelatedEntities::class,
            new AllowedMethod('getTags', new TagList(...$tags), $this->once()),
            new AllowedMethod('getPhotosList', new EntityPhotoList(...$photos), $this->once()),
            new AllowedMethod('getTimersList', new EntityTimerList(...$timers), $this->once()),
            new AllowedMethod('getGroupsList', new RecipePositionGroupList(...$groups), $this->once()),
        );

        return [$tags, $photos, $timers, $groups, $recipeFlags, $relatedEntities];
    }
}
