<?php

namespace App\Tests\Unit\Factory;

use App\Dto\Entity\Ingredient;
use App\Dto\Entity\List\IngredientList;
use App\Factory\Dto\DtoFactoryInterface;
use App\Factory\Dto\IngredientFactory;
use App\Factory\Dto\TagFactory;
use App\Factory\DtoFactoryDispatcher;
use App\Tests\Helper\AllowedMethod\AllowedCallbackMethod;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\TestCase\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;

#[Small]
#[CoversClass(DtoFactoryDispatcher::class)]
class DtoFactoryDispatcherTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Tworzy listę DTO, gdy istnieje przypisana fabryka')]
    public function itCreatesDtoListWhenFactoryExists(): void
    {
        // Arrange
        $dtoMock = $this->getMock(Ingredient::class);
        $entities = [
            new stdClass(),
            new stdClass(),
            new stdClass(),
        ];
        $params = [];
        $factory = $this->getMock(
            IngredientFactory::class,
            new AllowedMethod('getDtoName', Ingredient::class, $this->once()),
            new AllowedCallbackMethod(
                'get',
                function (...$args) use (&$params, $dtoMock): Ingredient {
                    $params[] = $args[0];
                    Assert::assertInstanceOf(DtoFactoryDispatcher::class, $args[1]);

                    return $dtoMock;
                },
                $this->exactly(3),
            ),
        );
        $factories = [$factory];
        $dtoFactoryDispatcher = new DtoFactoryDispatcher($factories);

        // Act
        $dtoList = $dtoFactoryDispatcher->getMany(IngredientList::class, ...$entities);

        // Assert
        $this->assertCount(3, $dtoList->get());
        foreach ($dtoList->get() as $dto) {
            $this->assertInstanceOf(Ingredient::class, $dto);
        }
    }

    #[Test]
    #[TestDox('Tworzy DTO, gdy istnieje przypisana fabryka')]
    public function itCreatesDtoWhenFactoryExists(): void
    {
        // Arrange
        $dtoMock = $this->createStub(Ingredient::class);
        $entity = new stdClass();
        $factory = $this->getMock(
            IngredientFactory::class,
            new AllowedMethod('getDtoName', Ingredient::class, $this->once()),
            new AllowedMethod(
                'get',
                $dtoMock,
                $this->once(),
                [$entity, $this->callback(fn ($arg): bool => $arg instanceof DtoFactoryDispatcher)],
            ),
        );
        $factories = [$factory];
        $dtoFactoryDispatcher = new DtoFactoryDispatcher($factories);

        // Act
        $dto = $dtoFactoryDispatcher->get(Ingredient::class, $entity);

        // Assert
        $this->assertInstanceOf(Ingredient::class, $dto);
    }

    #[Test]
    #[TestDox('Rzuca wyjątkiem gdy, fabryka nie implementuje interfejsu')]
    public function itThrowsExceptionWhenFactoryInvalid(): void
    {
        // Arrange
        $factories = [
            $this->createStub(DtoFactoryInterface::class),
            new stdClass(),
        ];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Factory must implement the "App\Factory\Dto\DtoFactoryInterface" interface.');

        // Act
        new DtoFactoryDispatcher($factories);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek przy próbie utworzenia listy nieznanych DTO')]
    public function itThrowsExceptionWhenUnknownDtoListRequested(): void
    {
        // Arrange
        $factories = [new TagFactory()];
        $factory = new DtoFactoryDispatcher($factories);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported DTO type:');

        // Act
        $factory->getMany(IngredientList::class);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek przy próbie utworzenia nieznanego DTO')]
    public function itThrowsExceptionWhenUnknownDtoRequested(): void
    {
        // Arrange
        $factories = [new TagFactory()];
        $factory = new DtoFactoryDispatcher($factories);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported DTO type:');

        // Act
        $factory->get(Ingredient::class, new stdClass());
    }
}
