<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ApiKey;
use App\Tests\Helper\TestCase\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(ApiKey::class)]
class ApiKeyTest extends BaseTestCase
{
    private ApiKey $apiKey;

    protected function setUp(): void
    {
        $this->apiKey = new ApiKey();
    }

    #[Test]
    #[TestDox('Uznaje klucz za poprawny, gdy długość jest właściwa')]
    public function itAcceptValidKeyLength(): void
    {
        // Assert
        $this->expectNotToPerformAssertions();

        // Act
        $this->apiKey->setKey(str_repeat('a', ApiKey::KEY_LENGTH));
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długi klucz')]
    public function itRejectsTooLongKey(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect key length');

        // Act
        $this->apiKey->setKey(str_repeat('a', ApiKey::KEY_LENGTH + 1));
    }

    #[Test]
    #[TestDox('Odrzuca zbyt krótki klucz')]
    public function itRejectsTooShortKey(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect key length');

        // Act
        $this->apiKey->setKey(str_repeat('a', ApiKey::KEY_LENGTH - 1));
    }
}
