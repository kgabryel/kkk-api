<?php

namespace App\Tests\Unit\Factory\Dto;

use App\Dto\Entity\Tag;
use App\Entity\Tag as TagEntity;
use App\Factory\Dto\TagFactory;
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
#[CoversClass(TagFactory::class)]
#[CoversClass(Tag::class)]
class TagFactoryTest extends BaseTestCase
{
    private DtoFactoryDispatcher $dtoFactoryDispatcher;
    private TagFactory $tagFactory;

    protected function setUp(): void
    {
        $this->tagFactory = new TagFactory();
        $this->dtoFactoryDispatcher = $this->getMock(DtoFactoryDispatcher::class);
    }

    #[Test]
    #[TestDox('Generuje DTO odpowiadające encji (Tag)')]
    #[DataProviderExternal(DtoFactoryDataProvider::class, 'tagValues')]
    public function itReturnsCorrectDto(array $entityData, Tag $expected): void
    {
        // Act
        $dto = $this->tagFactory->get($this->getEntity($entityData), $this->dtoFactoryDispatcher);

        // Assert
        $this->assertEquals($expected, $dto);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek, gdy przekazano obiekt złego typu - inną niż Tag')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'invalidEntitiesValues')]
    public function itThrowsExceptionForInvalidEntity(object $entity): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "entity" isn\'t an instance of');

        // Act
        $this->tagFactory->get($entity, $this->dtoFactoryDispatcher);
    }

    private function getEntity(array $data): TagEntity
    {
        return $this->getMock(
            TagEntity::class,
            new AllowedMethod('getId', $data[0], $this->atLeastOnce()),
            new AllowedMethod('getName', $data[1], $this->atLeastOnce()),
        );
    }
}
