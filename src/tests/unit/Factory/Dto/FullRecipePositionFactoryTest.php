<?php

namespace App\Tests\Unit\Factory\Dto;

use App\Dto\Entity\FullRecipePosition;
use App\Entity\RecipePosition as RecipePositionEntity;
use App\Factory\Dto\FullRecipePositionFactory;
use App\Factory\DtoFactoryDispatcher;
use App\Tests\DataProvider\DtoFactoryDataProvider;
use App\Tests\DataProvider\EntityFactoryDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(FullRecipePositionFactory::class)]
#[CoversClass(FullRecipePosition::class)]
class FullRecipePositionFactoryTest extends BaseTestCase
{
    private DtoFactoryDispatcher $dtoFactoryDispatcher;
    private FullRecipePositionFactory $fullRecipePositionFactory;

    protected function setUp(): void
    {
        $this->fullRecipePositionFactory = new FullRecipePositionFactory();
        $this->dtoFactoryDispatcher = $this->getMock(DtoFactoryDispatcher::class);
    }

    #[Test]
    #[TestDox('Generuje DTO odpowiadające encji (Recipe)')]
    #[DataProviderExternal(DtoFactoryDataProvider::class, 'fullRecipePositionValues')]
    public function itReturnsCorrectDto(array $entityData, FullRecipePosition $expected): void
    {
        // Act
        $dto = $this->fullRecipePositionFactory->get($this->getEntity($entityData), $this->dtoFactoryDispatcher);

        // Assert
        $this->assertEquals($expected, $dto);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek, gdy przekazano obiekt złego typu - inną niż Recipe')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'invalidEntitiesValues')]
    public function itThrowsExceptionForInvalidEntity(object $entity): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "entity" isn\'t an instance of');

        // Act
        $this->fullRecipePositionFactory->get($entity, $this->dtoFactoryDispatcher);
    }

    private function getEntity(array $data): RecipePositionEntity
    {
        $ingredient = null;
        if ($data[3]) {
            $ingredient = EntityFactory::getSimpleIngredient();
            $ingredient->setName($data[3]);
        }
        $recipe = null;
        if ($data[4]) {
            $recipe = EntityFactory::getSimpleRecipe();
            $recipe->setName($data[4]);
        }

        return $this->getMock(
            RecipePositionEntity::class,
            new AllowedMethod('getAmount', $data[0], $this->atLeastOnce()),
            new AllowedMethod('getMeasure', $data[1], $this->atLeastOnce()),
            new AllowedMethod('isAdditional', $data[2], $this->atLeastOnce()),
            new AllowedMethod('getIngredient', $ingredient, $this->atLeastOnce()),
            new AllowedMethod('getRecipe', $recipe, $this->atLeastOnce()),
        );
    }
}
