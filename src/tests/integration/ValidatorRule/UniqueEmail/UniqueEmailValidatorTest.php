<?php

namespace App\Tests\Integration\ValidatorRule\UniqueEmail;

use App\Repository\UserRepository;
use App\Tests\DataProvider\ValidationDataProvider;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use App\ValidatorRule\UniqueEmail\UniqueEmail;
use App\ValidatorRule\UniqueEmail\UniqueEmailValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Medium]
#[CoversClass(UniqueEmail::class)]
#[CoversClass(UniqueEmailValidator::class)]
class UniqueEmailValidatorTest extends BaseIntegrationTestCase
{
    private UniqueEmail $uniqueEmail;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();
        $this->validator = $container->get(ValidatorInterface::class);
        $userRepository = $container->get(UserRepository::class);
        $this->uniqueEmail = new UniqueEmail($userRepository);
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik, gdy email jest unikalny lub przypisany do użytkownika z fbId')]
    #[DataProviderExternal(ValidationDataProvider::class, 'acceptableEmailCases')]
    public function itPassesForValidEmails(string $emailToValidate, array $existingUsers): void
    {
        // Arrange
        foreach ($existingUsers as $userData) {
            $user = EntityFactory::createUser($userData['email']);
            $user->setFbId($userData['fbId']);
            $this->save($user);
        }

        // Act
        $violations = $this->validator->validate($emailToValidate, $this->uniqueEmail);

        // Assert
        $this->assertHasNoViolations($violations);
    }

    #[Test]
    #[TestDox('Odrzuca e-mail, gdy użytkownik standardowy z tym emailem już istnieje')]
    public function itRejectsWhenEmailExists(): void
    {
        // Arrange
        $this->defaultUser->setFbId(null);
        $this->save($this->defaultUser);

        // Act
        $violations = $this->validator->validate(EntityFactory::USER_EMAIL, $this->uniqueEmail);

        // Assert
        $this->assertHasOnlyOneViolationWithMessage($violations, 'This email is already in use.');
    }
}
