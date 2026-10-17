<?php

namespace App\Tests\Unit\Validation;

use App\Config\LengthConfig;
use App\Entity\User;
use App\Service\UserService;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\ChangePasswordValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validation;

#[Small]
#[CoversClass(ChangePasswordValidation::class)]
class ChangePasswordValidationTest extends ValidationTestCase
{
    private ChangePasswordValidation $changePasswordValidation;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = EntityFactory::getSimpleUser();
        $passwordHasher = $this->createStub(UserPasswordHasherInterface::class);
        $passwordHasher->method('isPasswordValid')->willReturn(true);
        $this->init($passwordHasher);
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'passwordChangePairsValues')]
    public function itAcceptsValidData(string $oldPassword, string $newPassword): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            [
                'newPassword' => ['first' => $newPassword, 'second' => $newPassword],
                'oldPassword' => $oldPassword,
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();
        $this->changePasswordValidation->getDto();

        // Assert
        $this->assertTrue($result->passed());
    }

    #[Test]
    #[TestDox('Nie następuje porównanie haseł jeżeli wystąpił błąd przy starym haśle')]
    public function itDoesNotComparePasswordsIfOldInvalid(): void
    {
        // Arrange
        $passwordHasher = $this->getMock(
            UserPasswordHasherInterface::class,
            new AllowedMethod('isPasswordValid', false, $this->once()),
        );
        $this->init($passwordHasher);
        $this->request->method('toArray')->willReturn(
            [
                'newPassword' => ['first' => 'old-password', 'second' => 'old-password'],
                'oldPassword' => 'old-password',
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[oldPassword]',
            ValidationErrors::INVALID_OLD_PASSWORD,
        );
        $this->assertFieldIsValid($result, '[newPassword]');
    }

    #[Test]
    #[TestDox('Nie pozwala pobrać DTO, gdy walidacja nie przeszła pomyślnie')]
    public function itRejectsDtoAccessWithoutValidation(): void
    {
        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(ValidationErrors::ACCESS_DTO_BEFORE_VALIDATION);

        // Act
        $this->changePasswordValidation->getDto();
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "newPassword.first"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyNewPassword(mixed $password): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            [
                'newPassword' => ['first' => $password, 'second' => 'password'],
                'oldPassword' => 'old-password',
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[newPassword][first]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "oldPassword"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyOldPassword(mixed $password): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            [
                'newPassword' => ['first' => 'password', 'second' => 'password'],
                'oldPassword' => $password,
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[oldPassword]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "newPassword.second"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyPasswordRepetition(mixed $password): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            [
                'newPassword' => ['first' => 'password', 'second' => $password],
                'oldPassword' => 'old-password',
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();

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
        $this->request->method('toArray')->willReturn(
            [
                'extra_field' => 'value',
                'newPassword' => ['first' => 'password', 'second' => 'password'],
                'oldPassword' => 'old-password',
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Stare hasło musi być poprawne')]
    public function itRejectsInvalidOldPassword(): void
    {
        // Arrange
        $passwordHasher = $this->getMock(
            UserPasswordHasherInterface::class,
            new AllowedMethod('isPasswordValid', false, $this->once(), [$this->user, 'old-password']),
        );
        $this->init($passwordHasher);
        $this->request->method('toArray')->willReturn(
            [
                'newPassword' => ['first' => 'password', 'second' => 'password'],
                'oldPassword' => 'old-password',
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[oldPassword]',
            ValidationErrors::INVALID_OLD_PASSWORD,
        );
    }

    #[Test]
    #[TestDox('Hasła muszą być identyczne')]
    public function itRejectsMismatchedPasswords(): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            [
                'newPassword' => ['first' => 'password1', 'second' => 'password2'],
                'oldPassword' => 'old-password',
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[newPassword][second]',
            ValidationErrors::PASSWORD_CONFIRMATION_MISMATCH,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "newPassword"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringNewPassword(mixed $password): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            [
                'newPassword' => ['first' => $password, 'second' => 'password'],
                'oldPassword' => 'old-password',
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[newPassword][first]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "oldPassword"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringOldPassword(mixed $password): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            [
                'newPassword' => ['first' => 'password', 'second' => 'password'],
                'oldPassword' => $password,
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[oldPassword]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długą wartość w polu "newPassword"')]
    public function itRejectsTooLongNewPassword(): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            [
                'newPassword' => [
                    'first' => str_repeat('a', LengthConfig::PASSWORD + 1),
                    'second' => 'password',
                ],
                'oldPassword' => 'old-password',
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[newPassword][first]',
            ValidationErrors::tooLong(255),
        );
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długą wartość w polu "oldPassword"')]
    public function itRejectsTooLongOldPassword(): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            [
                'newPassword' => ['first' => 'password', 'second' => 'password'],
                'oldPassword' => str_repeat('a', LengthConfig::PASSWORD + 1),
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[oldPassword]',
            ValidationErrors::tooLong(255),
        );
    }

    #[Test]
    #[TestDox('Nowe hasło nie może być takie samo jak stare')]
    public function itRejectsUnchangedPassword(): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            [
                'newPassword' => ['first' => 'password', 'second' => 'password'],
                'oldPassword' => 'password',
            ],
        );

        // Act
        $result = $this->changePasswordValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[newPassword][first]',
            ValidationErrors::PASSWORD_MUST_BE_DIFFER,
        );
    }

    private function init(UserPasswordHasherInterface $passwordHasher): void
    {
        $validator = Validation::createValidator();
        $userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $this->user, $this->once()),
        );
        $requestStack = new RequestStack([$this->request]);
        $this->changePasswordValidation = new ChangePasswordValidation(
            $validator,
            $requestStack,
            $userService,
            $passwordHasher,
        );
    }
}
