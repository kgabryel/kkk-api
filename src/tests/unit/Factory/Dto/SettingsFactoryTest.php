<?php

namespace App\Tests\Unit\Factory\Dto;

use App\Dto\Entity\Settings;
use App\Entity\Settings as SettingsEntity;
use App\Entity\User;
use App\Factory\Dto\SettingsFactory;
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
#[CoversClass(SettingsFactory::class)]
#[CoversClass(Settings::class)]
class SettingsFactoryTest extends BaseTestCase
{
    private DtoFactoryDispatcher $dtoFactoryDispatcher;
    private SettingsFactory $settingsFactory;

    protected function setUp(): void
    {
        $this->settingsFactory = new SettingsFactory();
        $this->dtoFactoryDispatcher = $this->getMock(DtoFactoryDispatcher::class);
    }

    #[Test]
    #[TestDox('Generuje DTO odpowiadające encji (Settings)')]
    #[DataProviderExternal(DtoFactoryDataProvider::class, 'settingsValues')]
    public function itReturnsCorrectDto(array $entityData, Settings $expected): void
    {
        // Act
        $dto = $this->settingsFactory->get($this->getEntity($entityData), $this->dtoFactoryDispatcher);

        // Assert
        $this->assertEquals($expected, $dto);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek, gdy przekazano obiekt złego typu - inną niż Settings')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'invalidEntitiesValues')]
    public function itThrowsExceptionForInvalidEntity(object $entity): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "entity" isn\'t an instance of');

        // Act
        $this->settingsFactory->get($entity, $this->dtoFactoryDispatcher);
    }

    private function getEntity(array $data): SettingsEntity
    {
        $user = $this->createStub(User::class);
        $user->method('getFbId')->willReturn($data[2]);

        return $this->getMock(
            SettingsEntity::class,
            new AllowedMethod('getAutocomplete', $data[0], $this->atLeastOnce()),
            new AllowedMethod('getOzaKey', $data[1], $this->atLeastOnce()),
            new AllowedMethod('getUser', $user, $this->atLeastOnce()),
        );
    }
}
