<?php

namespace App\Tests\Unit\Factory\Dto;

use App\Dto\Entity\ApiKey;
use App\Entity\ApiKey as ApiKeyEntity;
use App\Factory\Dto\ApiKeyFactory;
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
#[CoversClass(ApiKeyFactory::class)]
#[CoversClass(ApiKey::class)]
class ApiKeyFactoryTest extends BaseTestCase
{
    private ApiKeyFactory $apiKeyFactory;
    private DtoFactoryDispatcher $dtoFactoryDispatcher;

    protected function setUp(): void
    {
        $this->apiKeyFactory = new ApiKeyFactory();
        $this->dtoFactoryDispatcher = $this->getMock(DtoFactoryDispatcher::class);
    }

    #[Test]
    #[TestDox('Generuje DTO odpowiadające encji (ApiKey)')]
    #[DataProviderExternal(DtoFactoryDataProvider::class, 'apiKeyValues')]
    public function itReturnsCorrectDto(array $entityData, ApiKey $expected): void
    {
        // Act
        $dto = $this->apiKeyFactory->get($this->getEntity($entityData), $this->dtoFactoryDispatcher);

        // Assert
        $this->assertEquals($expected, $dto);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek, gdy przekazano obiekt złego typu - inną niż ApiKey')]
    #[DataProviderExternal(EntityFactoryDataProvider::class, 'invalidEntitiesValues')]
    public function itThrowsExceptionForInvalidEntity(object $entity): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "entity" isn\'t an instance of');

        // Act
        $this->apiKeyFactory->get($entity, $this->dtoFactoryDispatcher);
    }

    private function getEntity(array $data): ApiKeyEntity
    {
        return $this->getMock(
            ApiKeyEntity::class,
            new AllowedMethod('getId', $data[0], $this->atLeastOnce()),
            new AllowedMethod('getKey', $data[1], $this->atLeastOnce()),
            new AllowedMethod('isActive', $data[2], $this->atLeastOnce()),
        );
    }
}
