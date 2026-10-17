<?php

namespace App\Tests\Unit\Validation;

use App\Config\LengthConfig;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\ResetPasswordValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Small]
#[CoversClass(ResetPasswordValidation::class)]
class ResetPasswordValidationTest extends ValidationTestCase
{
    private UserPasswordHasherInterface $passwordHasher;
    private ResetPasswordValidation $resetPasswordValidation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->passwordHasher = $this->createStub(UserPasswordHasherInterface::class);
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validPasswordValues')]
    public function itAcceptsValidData(string $password): void
    {
        // Arrange
        $this->init(['newPassword' => ['first' => $password, 'second' => $password]]);

        // Act
        $result = $this->resetPasswordValidation->validate();
        $this->resetPasswordValidation->getDto(EntityFactory::getSimpleUser());

        // Assert
        $this->assertTrue($result->passed());
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy hasła nie są takie same')]
    public function itFailsValidationWhenPasswordsDoNotMatch(): void
    {
        // Arrange
        $this->init(['newPassword' => ['first' => 'password1', 'second' => 'password2']]);

        // Act
        $result = $this->resetPasswordValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[newPassword][second]',
            ValidationErrors::PASSWORD_CONFIRMATION_MISMATCH,
        );
    }

    #[Test]
    #[TestDox('Nie pozwala pobrać DTO, dy walidacja nie przeszła pomyślnie')]
    public function itRejectsDtoAccessWithoutValidation(): void
    {
        // Arrange
        $this->init([]);

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(ValidationErrors::ACCESS_DTO_BEFORE_VALIDATION);

        // Act
        $this->resetPasswordValidation->getDto(EntityFactory::getSimpleUser());
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "newPassword.first"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyPassword(mixed $password): void
    {
        // Arrange
        $this->init(['newPassword' => ['first' => $password, 'second' => 'password']]);

        // Act
        $result = $this->resetPasswordValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[newPassword][first]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "newPassword.second"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyPasswordRepetition(mixed $password): void
    {
        // Arrange
        $this->init(['newPassword' => ['first' => 'password', 'second' => $password]]);

        // Act
        $result = $this->resetPasswordValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[newPassword][second]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->init(['newPassword' => ['first' => 'password', 'second' => 'password'], 'extra_field' => 'value']);

        // Act
        $result = $this->resetPasswordValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "password"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringPassword(mixed $password): void
    {
        // Arrange
        $this->init(['newPassword' => ['first' => $password, 'second' => 'password']]);

        // Act
        $result = $this->resetPasswordValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[newPassword][first]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długą wartość w polu "password"')]
    public function itRejectsTooLongPassword(): void
    {
        // Arrange
        $this->init(
            ['newPassword' => [
                'first' => str_repeat('a', LengthConfig::PASSWORD + 1),
                'second' => 'password',
            ],
            ],
        );

        // Act
        $result = $this->resetPasswordValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[newPassword][first]',
            ValidationErrors::tooLong(LengthConfig::PASSWORD),
        );
    }

    private function init(array $requestData): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->resetPasswordValidation = new ResetPasswordValidation(
            $this->validator,
            $this->requestStack,
            $this->passwordHasher,
        );
    }
}
