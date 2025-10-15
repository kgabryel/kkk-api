<?php

namespace App\Tests\Unit\Dto\Entity;

use App\Dto\Entity\FullRecipe;
use App\Dto\Entity\List\FullRecipePositionsGroupList;
use App\Dto\Entity\List\PhotoList;
use App\Dto\Helper\RecipeRelatedEntities;
use App\Dto\List\Entity\PhotoList as EntityPhotoList;
use App\Dto\List\Entity\RecipePositionGroupList;
use App\Dto\List\Entity\TagList;
use App\Entity\Photo;
use App\Entity\RecipePositionGroup;
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
#[CoversClass(FullRecipe::class)]
class FullRecipeTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Tworzy DTO z użyciem dispatcherów')]
    public function itBuildsDto(): void
    {
        // Arrange
        [$tags, $photos, $groups] = $this->createRecipeDependencies([1 => 'tag1', 2 => 'tag2'], [1, 2], [1]);
        $expected = [
            FullRecipePositionsGroupList::class => [FullRecipePositionsGroupList::class, ...$groups],
            PhotoList::class => [PhotoList::class, ...$photos],
        ];
        $relatedEntities = $this->getMock(
            RecipeRelatedEntities::class,
            new AllowedMethod('getTags', new TagList(...$tags), $this->once()),
            new AllowedMethod('getPhotosList', new EntityPhotoList(...$photos), $this->once()),
            new AllowedMethod('getGroupsList', new RecipePositionGroupList(...$groups), $this->once()),
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
                $this->exactly(2),
            ),
        );

        // Act
        $dto = new FullRecipe(
            1,
            'name',
            'description',
            null,
            1,
            $relatedEntities,
            $dtoFactoryDispatcher,
        );

        // Assert
        $this->assertCount(count($expected), $parsed);
        foreach ($expected as $key => $expectedValue) {
            $this->assertSame($expectedValue, $parsed[$key]);
        }
        $this->assertSame(['TAG1', 'TAG2'], $dto->jsonSerialize()['tags']);
    }

    private function createRecipeDependencies(array $tags, array $photoIds, array $groupIds): array
    {
        $tmp = [];
        foreach ($tags as $id => $name) {
            $tmp[] = EntityFactory::getSimpleTag($id)->setName($name);
        }
        $photos = array_map(static fn (int $id): Photo => EntityFactory::getSimplePhoto($id), $photoIds);
        $groups = array_map(
            static fn (int $id): RecipePositionGroup => EntityFactory::getSimpleRecipePositionGroup($id),
            $groupIds,
        );

        return [$tmp, $photos, $groups];
    }
}
