<?php

namespace App\Tests\Unit\Factory\Dto;

use App\Dto\Entity\RecipePositionsGroup;
use App\Factory\Dto\RecipePositionsGroupFactory;
use App\Factory\DtoFactoryDispatcher;
use App\Tests\DataProvider\EntityFactoryDataProvider;
use App\Tests\Helper\TestCase\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(RecipePositionsGroupFactory::class)]
#[CoversClass(RecipePositionsGroup::class)]
class RecipePositionsGroupFactoryTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Rzuca wyjątek, gdy przekazano obiekt złego typu - inną niż RecipePositionGroup')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'invalidEntitiesValues')]
    public function itThrowsExceptionForInvalidEntity(object $entity): void
    {
        // Arrange
        $factory = new RecipePositionsGroupFactory();
        $dtoFactoryDispatcher = $this->getMock(DtoFactoryDispatcher::class);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "entity" isn\'t an instance of');

        // Act
        $factory->get($entity, $dtoFactoryDispatcher);
    }
}
