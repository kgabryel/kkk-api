<?php

namespace App\Tests\Unit\Factory\Dto;

use App\Dto\Entity\OzaSupply;
use App\Factory\Dto\OzaSupplyFactory;
use App\Factory\DtoFactoryDispatcher;
use App\Tests\DataProvider\DtoFactoryDataProvider;
use App\Tests\Helper\TestCase\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(OzaSupplyFactory::class)]
#[CoversClass(OzaSupply::class)]
class OzaSupplyFactoryTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Generuje DTO na podstawie przekazanych danych')]
    #[DataProviderExternal(DtoFactoryDataProvider::class, 'ozaSupplyValues')]
    public function itReturnsCorrectDto(object $entityData, OzaSupply $expected): void
    {
        // Arrange
        $factory = new OzaSupplyFactory();
        $dtoFactoryDispatcher = $this->getMock(DtoFactoryDispatcher::class);

        // Act
        $dto = $factory->get($entityData, $dtoFactoryDispatcher);

        // Assert
        $this->assertEquals($expected, $dto);
    }
}
