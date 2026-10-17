<?php

namespace App\Tests\Unit\Factory\Dto;

use App\Dto\Entity\Season;
use App\Entity\Season as SeasonEntity;
use App\Factory\Dto\SeasonFactory;
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
#[CoversClass(SeasonFactory::class)]
#[CoversClass(Season::class)]
class SeasonFactoryTest extends BaseTestCase
{
    private DtoFactoryDispatcher $dtoFactoryDispatcher;
    private SeasonFactory $seasonFactory;

    protected function setUp(): void
    {
        $this->seasonFactory = new SeasonFactory();
        $this->dtoFactoryDispatcher = $this->getMock(DtoFactoryDispatcher::class);
    }

    #[Test]
    #[TestDox('Generuje DTO odpowiadające encji (Season)')]
    #[DataProviderExternal(DtoFactoryDataProvider::class, 'seasonValues')]
    public function itReturnsCorrectDto(array $entityData, Season $expected): void
    {
        // Act
        $dto = $this->seasonFactory->get($this->getEntity($entityData), $this->dtoFactoryDispatcher);

        // Assert
        $this->assertEquals($expected, $dto);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek, gdy przekazano obiekt złego typu - inną niż Season')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'invalidEntitiesValues')]
    public function itThrowsExceptionForInvalidEntity(object $entity): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "entity" isn\'t an instance of');

        // Act
        $this->seasonFactory->get($entity, $this->dtoFactoryDispatcher);
    }

    private function getEntity(array $data): SeasonEntity
    {
        return $this->getMock(
            SeasonEntity::class,
            new AllowedMethod('getId', $data[0], $this->atLeastOnce()),
            new AllowedMethod(
                'getIngredient',
                EntityFactory::getSimpleIngredient($data[1]),
                $this->atLeastOnce(),
            ),
            new AllowedMethod('getStart', $data[2], $this->atLeastOnce()),
            new AllowedMethod('getStop', $data[3], $this->atLeastOnce()),
        );
    }
}
