<?php

namespace App\Tests\Unit\Factory\Dto;

use App\Dto\Entity\RecipePosition;
use App\Entity\RecipePosition as RecipePositionEntity;
use App\Factory\Dto\RecipePositionFactory;
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
#[CoversClass(RecipePositionFactory::class)]
#[CoversClass(RecipePosition::class)]
class RecipePositionFactoryTest extends BaseTestCase
{
    private DtoFactoryDispatcher $dtoFactoryDispatcher;
    private RecipePositionFactory $recipePositionFactory;

    protected function setUp(): void
    {
        $this->recipePositionFactory = new RecipePositionFactory();
        $this->dtoFactoryDispatcher = $this->getMock(DtoFactoryDispatcher::class);
    }

    #[Test]
    #[TestDox('Generuje DTO odpowiadające encji (RecipePosition)')]
    #[DataProviderExternal(DtoFactoryDataProvider::class, 'recipePositionValues')]
    public function itReturnsCorrectDto(array $entityData, RecipePosition $expected): void
    {
        // Act
        $dto = $this->recipePositionFactory->get($this->getEntity($entityData), $this->dtoFactoryDispatcher);

        // Assert
        $this->assertEquals($expected, $dto);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek, gdy przekazano obiekt złego typu - inną niż RecipePosition')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'invalidEntitiesValues')]
    public function itThrowsExceptionForInvalidEntity(object $entity): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "entity" isn\'t an instance of');

        // Act
        $this->recipePositionFactory->get($entity, $this->dtoFactoryDispatcher);
    }

    private function getEntity(array $data): RecipePositionEntity
    {
        $allowedMethods = [
            new AllowedMethod('getAmount', $data[0], $this->atLeastOnce()),
            new AllowedMethod('getMeasure', $data[1], $this->atLeastOnce()),
            new AllowedMethod('isAdditional', $data[4], $this->atLeastOnce()),
        ];
        if ($data[2] !== null) {
            $allowedMethods[] = new AllowedMethod(
                'getIngredient',
                EntityFactory::getSimpleIngredient($data[2]),
                $this->atLeastOnce(),
            );
        } else {
            $allowedMethods[] = new AllowedMethod('getIngredient', $data[2], $this->atLeastOnce());
        }
        if ($data[3] !== null) {
            $allowedMethods[] = new AllowedMethod(
                'getRecipe',
                EntityFactory::getSimpleRecipe($data[3]),
                $this->atLeastOnce(),
            );
        } else {
            $allowedMethods[] = new AllowedMethod('getRecipe', $data[3], $this->atLeastOnce());
        }

        return $this->getMock(RecipePositionEntity::class, ...$allowedMethods);
    }
}
