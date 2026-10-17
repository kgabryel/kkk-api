<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\List\ApiKeyList;
use App\Response\ApiKeyListResponse;
use App\Tests\DataProvider\SimpleEntityDataProvider;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(ApiKeyListResponse::class)]
class ApiKeyListResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy listę DTO na podstawie encji - ApiKey -> ApiKeyList')]
    #[DataProviderExternal(SimpleEntityDataProvider::class, 'apiKeysValues')]
    public function itCallsDtoFactoryWithCorrectParams(array $apiKeys): void
    {
        // Arrange
        $this->setupFactoryForList(new ApiKeyList(), [ApiKeyList::class, ...$apiKeys]);

        // Act
        new ApiKeyListResponse($this->dtoFactory, ...$apiKeys);
    }
}
