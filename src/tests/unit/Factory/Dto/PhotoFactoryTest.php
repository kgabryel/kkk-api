<?php

namespace App\Tests\Unit\Factory\Dto;

use App\Dto\Entity\Photo;
use App\Entity\Photo as PhotoEntity;
use App\Factory\Dto\PhotoFactory;
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
#[CoversClass(PhotoFactory::class)]
#[CoversClass(Photo::class)]
class PhotoFactoryTest extends BaseTestCase
{
    private DtoFactoryDispatcher $dtoFactoryDispatcher;
    private PhotoFactory $photoFactory;

    protected function setUp(): void
    {
        $this->photoFactory = new PhotoFactory();
        $this->dtoFactoryDispatcher = $this->getMock(DtoFactoryDispatcher::class);
    }

    #[Test]
    #[TestDox('Generuje DTO odpowiadające encji (Photo)')]
    #[DataProviderExternal(DtoFactoryDataProvider::class, 'photoValues')]
    public function itReturnsCorrectDto(array $entityData, Photo $expected): void
    {
        // Act
        $dto = $this->photoFactory->get($this->getEntity($entityData), $this->dtoFactoryDispatcher);

        // Assert
        $this->assertEquals($expected, $dto);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek, gdy przekazano obiekt złego typu - inną niż Photo')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'invalidEntitiesValues')]
    public function itThrowsExceptionForInvalidEntity(object $entity): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "entity" isn\'t an instance of');

        // Act
        $this->photoFactory->get($entity, $this->dtoFactoryDispatcher);
    }

    private function getEntity(array $data): PhotoEntity
    {
        return $this->getMock(
            PhotoEntity::class,
            new AllowedMethod('getId', $data[0], $this->atLeastOnce()),
            new AllowedMethod('getWidth', $data[1], $this->atLeastOnce()),
            new AllowedMethod('getHeight', $data[2], $this->atLeastOnce()),
            new AllowedMethod('getType', $data[3], $this->atLeastOnce()),
        );
    }
}
