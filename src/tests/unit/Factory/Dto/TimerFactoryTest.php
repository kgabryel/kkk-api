<?php

namespace App\Tests\Unit\Factory\Dto;

use App\Dto\Entity\Timer;
use App\Entity\Timer as TimerEntity;
use App\Factory\Dto\TimerFactory;
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
#[CoversClass(TimerFactory::class)]
#[CoversClass(Timer::class)]
class TimerFactoryTest extends BaseTestCase
{
    private DtoFactoryDispatcher $dtoFactoryDispatcher;
    private TimerFactory $timerFactory;

    protected function setUp(): void
    {
        $this->timerFactory = new TimerFactory();
        $this->dtoFactoryDispatcher = $this->getMock(DtoFactoryDispatcher::class);
    }

    #[Test]
    #[TestDox('Generuje DTO odpowiadające encji (Timer)')]
    #[DataProviderExternal(DtoFactoryDataProvider::class, 'timerValues')]
    public function itReturnsCorrectDto(array $entityData, Timer $expected): void
    {
        // Act
        $dto = $this->timerFactory->get($this->getEntity($entityData), $this->dtoFactoryDispatcher);

        // Assert
        $this->assertEquals($expected, $dto);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek, gdy przekazano obiekt złego typu - inną niż Timer')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'invalidEntitiesValues')]
    public function itThrowsExceptionForInvalidEntity(object $entity): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "entity" isn\'t an instance of');

        // Act
        $this->timerFactory->get($entity, $this->dtoFactoryDispatcher);
    }

    private function getEntity(array $data): TimerEntity
    {
        return $this->getMock(
            TimerEntity::class,
            new AllowedMethod('getId', $data[0], $this->atLeastOnce()),
            new AllowedMethod('getName', $data[1], $this->atLeastOnce()),
            new AllowedMethod('getTime', $data[2], $this->atLeastOnce()),
        );
    }
}
