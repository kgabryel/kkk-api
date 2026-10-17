<?php

namespace App\Tests\Unit\Response;

use App\Dto\Entity\ApiKey;
use App\Response\ApiKeyResponse;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ResponseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(ApiKeyResponse::class)]
class ApiKeyResponseTest extends ResponseTestCase
{
    #[Test]
    #[TestDox('Tworzy DTO na podstawie encji - ApiKey')]
    public function itCallsDtoFactoryWithCorrectParams(): void
    {
        // Arrange
        $apiKey = EntityFactory::getSimpleApiKey();
        $this->setupFactoryForSingleEntity([ApiKey::class, $apiKey]);

        // Act
        new ApiKeyResponse($this->dtoFactory, $apiKey, Response::HTTP_OK);
    }
}
