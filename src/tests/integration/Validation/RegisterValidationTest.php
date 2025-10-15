<?php

namespace App\Tests\Integration\Validation;

use App\Repository\UserRepository;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use App\Validation\RegisterValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Medium]
#[CoversClass(RegisterValidation::class)]
class RegisterValidationTest extends BaseIntegrationTestCase
{
    private RegisterValidation $registerValidation;
    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();
        $container = self::getContainer();
        $validator = $container->get(ValidatorInterface::class);
        $this->request = $this->createStub(Request::class);
        $requestStack = new RequestStack([$this->request]);
        $userRepository = $container->get(UserRepository::class);
        $userPasswordHasher = $container->get(UserPasswordHasherInterface::class);
        $this->registerValidation = new RegisterValidation(
            $validator,
            $requestStack,
            $userRepository,
            $userPasswordHasher,
        );
    }

    #[Test]
    #[TestDox('Zwraca pozytywny wynik dla poprawnych danych')]
    public function itAcceptsValidData(): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            ['email' => EntityFactory::USER_EMAIL_2, 'password' => ['first' => 'password', 'second' => 'password']],
        );

        // Act
        $result = $this->registerValidation->validate();

        // Assert
        $this->assertHasNoViolations($result->getRawErrors());
    }

    #[Test]
    #[TestDox('Walidacja kończy się błędem, gdy e-mail jest już wykorzystywany')]
    public function itFailsWhenEmailIsAlreadyUsed(): void
    {
        // Arrange
        $this->request->method('toArray')->willReturn(
            ['email' => EntityFactory::USER_EMAIL, 'password' => ['first' => 'password', 'second' => 'password']],
        );

        // Act
        $result = $this->registerValidation->validate();

        // Assert
        $this->assertHasOnlyOneViolationWithMessage(
            $result->getRawErrors(),
            'Email is already in use.',
        );
    }
}
