<?php

namespace App\Tests\Unit\Validation;

use App\Config\LengthConfig;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\OzaKeyValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;

#[Small]
#[CoversClass(OzaKeyValidation::class)]
class OzaKeyValidationTest extends ValidationTestCase
{
    private OzaKeyValidation $ozaKeyValidation;

    #[Test]
    #[TestDox('Pole "key" może być nullem')]
    public function itAcceptsNullKey(): void
    {
        // Arrange
        $this->init(['key' => null]);

        // Act
        $result = $this->ozaKeyValidation->validate();

        // Assert
        $this->assertTrue($result->passed());
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validOzaKeyValues')]
    public function itAcceptsValidData(?string $key): void
    {
        // Arrange
        $this->init(['key' => $key]);

        // Act
        $result = $this->ozaKeyValidation->validate();
        $this->ozaKeyValidation->getDto();

        // Assert
        $this->assertTrue($result->passed());
    }

    #[Test]
    #[TestDox('Nie pozwala pobrać DTO, gdy walidacja nie przeszła pomyślnie')]
    public function itRejectsDtoAccessWithoutValidation(): void
    {
        // Arrange
        $this->init([]);

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(ValidationErrors::ACCESS_DTO_BEFORE_VALIDATION);

        // Act
        $this->ozaKeyValidation->getDto();
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->init(['key' => 'Valid key', 'extra_field' => 'value']);

        // Act
        $result = $this->ozaKeyValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "key"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringKey(mixed $key): void
    {
        // Arrange
        $this->init(['key' => $key]);

        // Act
        $result = $this->ozaKeyValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[key]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długą wartość w polu "key"')]
    public function itRejectsTooLongKey(): void
    {
        // Arrange
        $this->init(['key' => str_repeat('a', LengthConfig::OZA_KEY + 1)]);

        // Act
        $result = $this->ozaKeyValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[key]',
            ValidationErrors::tooLong(LengthConfig::OZA_KEY),
        );
    }

    private function init(array $requestData): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->ozaKeyValidation = new OzaKeyValidation(
            $this->validator,
            $this->requestStack,
            $this->userService,
        );
    }
}
