<?php

namespace App\Tests\Unit\Validation;

use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\FbLoginValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;

#[Small]
#[CoversClass(FbLoginValidation::class)]
class FbLoginValidationTest extends ValidationTestCase
{
    private FbLoginValidation $fbLoginValidation;

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validTokenValues')]
    public function itAcceptsValidData(string $token): void
    {
        // Arrange
        $this->init(['authToken' => $token]);

        // Act
        $result = $this->fbLoginValidation->validate();
        $this->fbLoginValidation->getDto();

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
        $this->fbLoginValidation->getDto();
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "authToken"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyAuthToken(mixed $token): void
    {
        // Arrange
        $this->init(['authToken' => $token]);

        // Act
        $result = $this->fbLoginValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[authToken]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->init(['authToken' => 'valida token', 'extra_field' => 'value']);

        // Act
        $result = $this->fbLoginValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "authToken"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringAuthToken(mixed $token): void
    {
        $this->init(['authToken' => $token]);

        // Act
        $result = $this->fbLoginValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[authToken]',
            ValidationErrors::TYPE_STRING,
        );
    }

    private function init(array $requestData): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->fbLoginValidation = new FbLoginValidation($this->validator, $this->requestStack);
    }
}
