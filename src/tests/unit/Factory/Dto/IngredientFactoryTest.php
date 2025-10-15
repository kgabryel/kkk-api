<?php

namespace App\Tests\Unit\Factory\Dto;

use App\Dto\Entity\Ingredient;
use App\Entity\Ingredient as IngredientEntity;
use App\Factory\Dto\IngredientFactory;
use App\Factory\DtoFactoryDispatcher;
use App\Tests\DataProvider\DtoFactoryDataProvider;
use App\Tests\DataProvider\EntityFactoryDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\TestCase\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(IngredientFactory::class)]
#[CoversClass(Ingredient::class)]
class IngredientFactoryTest extends BaseTestCase
{
    private DtoFactoryDispatcher $dtoFactoryDispatcher;
    private IngredientFactory $ingredientFactory;

    protected function setUp(): void
    {
        $this->ingredientFactory = new IngredientFactory();
        $this->dtoFactoryDispatcher = $this->getMock(DtoFactoryDispatcher::class);
    }

    #[Test]
    #[TestDox('Generuje DTO odpowiadające encji (Ingredient)')]
    #[DataProviderExternal(DtoFactoryDataProvider::class, 'ingredientValues')]
    public function itReturnsCorrectDto(array $entityData, Ingredient $expected): void
    {
        // Act
        $dto = $this->ingredientFactory->get($this->getEntity($entityData), $this->dtoFactoryDispatcher);

        // Assert
        $this->assertEquals($expected, $dto);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek, gdy przekazano obiekt złego typu - inną niż Ingredient')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'invalidEntitiesValues')]
    public function itThrowsExceptionForInvalidEntity(object $entity): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "entity" isn\'t an instance of');

        // Act
        $this->ingredientFactory->get($entity, $this->dtoFactoryDispatcher);
    }

    private function getEntity(array $data): IngredientEntity
    {
        return $this->getMock(
            IngredientEntity::class,
            new AllowedMethod('getId', $data[0], $this->atLeastOnce()),
            new AllowedMethod('getName', $data[1], $this->atLeastOnce()),
            new AllowedMethod('isAvailable', $data[2], $this->atLeastOnce()),
            new AllowedMethod('getOzaId', $data[3], $this->atLeastOnce()),
        );
    }
}
