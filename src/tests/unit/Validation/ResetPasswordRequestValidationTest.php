<?php

namespace App\Tests\Unit\Validation;

use App\Config\LengthConfig;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\ResetPasswordRequestValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;

#[Small]
#[CoversClass(ResetPasswordRequestValidation::class)]
class ResetPasswordRequestValidationTest extends ValidationTestCase
{
    private ResetPasswordRequestValidation $resetPasswordRequestValidation;

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validEmailValues')]
    public function itAcceptsValidData(string $email): void
    {
        // Arrange
        $this->init(['email' => $email]);

        // Act
        $result = $this->resetPasswordRequestValidation->validate();
        $this->resetPasswordRequestValidation->getDto();

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
        $this->resetPasswordRequestValidation->getDto();
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "email"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyEmail(mixed $email): void
    {
        // Arrange
        $this->init(['email' => $email]);

        // Act
        $result = $this->resetPasswordRequestValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[email]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->init(['email' => EntityFactory::USER_EMAIL, 'extra_field' => 'value']);

        // Act
        $result = $this->resetPasswordRequestValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Pole email powinno przepuszczać tylko poprawne adresy e-mail')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidEmails')]
    public function itRejectsInvalidEmailFormat(mixed $email): void
    {
        $this->init(['email' => $email]);

        // Act
        $result = $this->resetPasswordRequestValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[email]',
            ValidationErrors::INVALID_EMAIL,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "email"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringEmail(mixed $email): void
    {
        $this->init(['email' => $email]);

        // Act
        $result = $this->resetPasswordRequestValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[email]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długą wartość w polu "email"')]
    public function itRejectsTooLongEmail(): void
    {
        // Arrange
        $this->init(['email' => str_repeat('a', LengthConfig::EMAIL + 1)]);

        // Act
        $result = $this->resetPasswordRequestValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[email]',
            ValidationErrors::tooLong(LengthConfig::EMAIL),
        );
    }

    private function init(array $requestData): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->resetPasswordRequestValidation = new ResetPasswordRequestValidation(
            $this->validator,
            $this->requestStack,
        );
    }
}
