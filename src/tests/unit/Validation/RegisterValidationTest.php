<?php

namespace App\Tests\Unit\Validation;

use App\Config\LengthConfig;
use App\Repository\UserRepository;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\ValidationTestCase;
use App\Tests\Helper\ValidationErrors;
use App\Validation\RegisterValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Small]
#[CoversClass(RegisterValidation::class)]
class RegisterValidationTest extends ValidationTestCase
{
    private UserPasswordHasherInterface $passwordHasher;
    private RegisterValidation $registerValidation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->passwordHasher = $this->getMock(UserPasswordHasherInterface::class);
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    #[DataProviderExternal(ValidationDataProvider::class, 'validRegisterValues')]
    public function itAcceptsValidData(string $email, string $first, string $second): void
    {
        // Arrange
        $this->init(['email' => $email, 'password' => ['first' => $first, 'second' => $second]]);

        // Act
        $result = $this->registerValidation->validate();
        $this->registerValidation->getDto();

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
        $this->registerValidation->getDto();
    }

    #[Test]
    #[TestDox('Odrzuca istniejący adres e-mail zwykłego użytkownika')]
    public function itRejectsEmailOfExistingUser(): void
    {
        // Arrange
        $this->init(
            ['email' => EntityFactory::USER_EMAIL, 'password' => ['first' => 'password', 'second' => 'password']],
            $this->getMock(
                UserRepository::class,
                new AllowedMethod(
                    'findOneBy',
                    EntityFactory::getSimpleUser(),
                    $this->once(),
                    [
                        [
                            'email' => EntityFactory::USER_EMAIL,
                            'fbId' => null,
                        ],
                    ],
                ),
            ),
        );

        // Act
        $result = $this->registerValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[email]',
            ValidationErrors::EMAIL_ALREADY_IN_USE,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "e-mail"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyEmail(mixed $email): void
    {
        // Arrange
        $this->init(['email' => $email, 'password' => ['first' => 'password', 'second' => 'password']]);

        // Act
        $result = $this->registerValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[email]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "password.first"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyPassword(mixed $password): void
    {
        // Arrange
        $this->init(
            ['email' => EntityFactory::USER_EMAIL, 'password' => ['first' => $password, 'second' => 'password']],
        );

        // Act
        $result = $this->registerValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[password][first]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Odrzuca puste pole "password.second"')]
    #[DataProviderExternal(CommonDataProvider::class, 'blankValues')]
    public function itRejectsEmptyPasswordRepetition(mixed $password): void
    {
        // Arrange
        $this->init(
            ['email' => EntityFactory::USER_EMAIL, 'password' => ['first' => 'password', 'second' => $password]],
        );

        // Act
        $result = $this->registerValidation->validate();

        // Assert
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[password][second]',
            ValidationErrors::NOT_BLANK,
        );
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy pojawią się dodatkowe pola')]
    public function itRejectsExtraFields(): void
    {
        // Arrange
        $this->init(
            [
                'email' => EntityFactory::USER_EMAIL,
                'extra_field' => 'value',
                'password' => ['first' => 'password', 'second' => 'password'],
            ],
        );

        // Act
        $result = $this->registerValidation->validate();

        // Assert
        $this->assertHasExtraFieldError($result);
    }

    #[Test]
    #[TestDox('Błędny format adresu e-mail powinien spowodować błąd')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidEmails')]
    public function itRejectsInvalidEmailFormat(string $email): void
    {
        // Arrange
        $this->init(['email' => $email, 'password' => ['first' => 'password', 'second' => 'password']]);

        // Act
        $result = $this->registerValidation->validate();

        // Act
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
        // Arrange
        $this->init(['email' => $email, 'password' => ['first' => 'password', 'second' => 'password']]);

        // Act
        $result = $this->registerValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[email]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Odrzuca nie-stringi w polu "password"')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidNonBlankStringValues')]
    public function itRejectsNonStringPassword(mixed $password): void
    {
        // Arrange
        $this->init(
            ['email' => EntityFactory::USER_EMAIL, 'password' => ['first' => $password, 'second' => 'password']],
        );

        // Act
        $result = $this->registerValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[password][first]',
            ValidationErrors::TYPE_STRING,
        );
    }

    #[Test]
    #[TestDox('Hasła muszą być takie same')]
    public function itRejectsPasswordMismatch(): void
    {
        // Arrange
        $this->init(
            [
                'email' => EntityFactory::USER_EMAIL,
                'password' => ['first' => 'password1', 'second' => 'password2'],
            ],
        );

        // Act
        $result = $this->registerValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[password][second]',
            ValidationErrors::PASSWORD_CONFIRMATION_MISMATCH,
        );
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długą wartość w polu "email"')]
    public function itRejectsTooLongEmail(): void
    {
        // Arrange
        $this->init(
            [
                'email' => str_repeat('a', LengthConfig::EMAIL + 1),
                'password' => ['first' => 'password', 'second' => 'password'],
            ],
        );

        // Act
        $result = $this->registerValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[email]',
            ValidationErrors::tooLong(LengthConfig::EMAIL),
        );
    }

    #[Test]
    #[TestDox('Odrzuca zbyt długą wartość w polu "password"')]
    public function itRejectsTooLongPassword(): void
    {
        // Arrange
        $this->init(
            [
                'email' => EntityFactory::USER_EMAIL,
                'password' => [
                    'first' => str_repeat('a', LengthConfig::PASSWORD + 1),
                    'second' => 'password',
                ],
            ],
        );

        // Act
        $result = $this->registerValidation->validate();

        // Act
        $this->assertFieldHasOnlyOneErrorWithMessage(
            $result,
            '[password][first]',
            ValidationErrors::tooLong(LengthConfig::PASSWORD),
        );
    }

    private function init(array $requestData, ?UserRepository $userRepository = null): void
    {
        $this->request->method('toArray')->willReturn($requestData);
        $this->registerValidation = new RegisterValidation(
            $this->validator,
            $this->requestStack,
            $userRepository ?? $this->createStub(UserRepository::class),
            $this->passwordHasher,
        );
    }
}
