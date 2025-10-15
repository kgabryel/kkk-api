<?php

namespace App\Tests\Unit\Dto\Entity;

use App\Dto\Entity\Settings;
use App\Tests\DataProvider\UserDataProvider;
use App\Tests\Helper\TestCase\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(Settings::class)]
class SettingsTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Zwraca poprawny userType przy serializacji')]
    #[DataProviderExternal(UserDataProvider::class, 'userTypesValues')]
    public function itReturnsCorrectUserType(bool $isStandardUser, string $userType): void
    {
        // Arrange
        $dto = new Settings(false, null, $isStandardUser);

        // Act
        $jsonValue = $dto->jsonSerialize();

        // Assert
        $this->assertSame($userType, $jsonValue['userType']);
    }
}
