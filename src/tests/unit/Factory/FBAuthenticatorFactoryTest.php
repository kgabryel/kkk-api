<?php

namespace App\Tests\Unit\Factory;

use App\Factory\FBAuthenticatorFactory;
use App\Repository\UserRepository;
use App\Service\Auth\RegistrationService;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMapValueMethod;
use App\Tests\Helper\TestCase\BaseTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Small]
#[CoversClass(FBAuthenticatorFactory::class)]
class FBAuthenticatorFactoryTest extends BaseTestCase
{
    private EntityManagerInterface $entityManager;
    private RegistrationService $registrationService;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = $this->getMock(UserRepository::class);
        $this->registrationService = $this->getMock(RegistrationService::class);
        $this->entityManager = $this->getMock(EntityManagerInterface::class);
    }

    #[Test]
    #[TestDox('Tworzy poprawny FBAuthenticator')]
    public function itCreatesAuthenticator(): void
    {
        // /Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMapValueMethod(
                'has',
                [
                    ['FB_ID', true],
                    ['FB_SECRET', true],
                    ['FB_REDIRECT', true],
                ],
            ),
            new AllowedMapValueMethod(
                'get',
                [
                    ['FB_ID', 'id'],
                    ['FB_SECRET', 'secret'],
                    ['FB_REDIRECT', 'redirect'],
                ],
            ),
        );
        $factory = new FBAuthenticatorFactory(
            $this->userRepository,
            $this->entityManager,
            $parameterBag,
            $this->registrationService,
        );

        // Act
        $factory->create();
    }

    #[Test]
    #[TestDox('Odrzuca konfigurację, gdy FB_ID jest pusty lub nieprawidłowy')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidConfigValues')]
    public function itFailsWhenFbIdIsInvalid(mixed $invalidConfig): void
    {
        // Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMapValueMethod(
                'has',
                [
                    ['FB_ID', true],
                    ['FB_SECRET', true],
                    ['FB_REDIRECT', true],
                ],
            ),
            new AllowedMapValueMethod(
                'get',
                [
                    ['FB_ID', $invalidConfig],
                    ['FB_SECRET', 'secret'],
                    ['FB_REDIRECT', 'url'],
                ],
            ),
        );
        $factory = new FBAuthenticatorFactory(
            $this->userRepository,
            $this->entityManager,
            $parameterBag,
            $this->registrationService,
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('FB_ID parameter is empty or invalid.');

        // Act
        $factory->create();
    }

    #[Test]
    #[TestDox('Odrzuca konfigurację, gdy FB_ID nie jest ustawione')]
    public function itFailsWhenFbIdIsMissing(): void
    {
        // Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMapValueMethod(
                'has',
                [
                    ['FB_ID', false],
                    ['FB_SECRET', true],
                    ['FB_REDIRECT', true],
                ],
            ),
        );
        $factory = new FBAuthenticatorFactory(
            $this->userRepository,
            $this->entityManager,
            $parameterBag,
            $this->registrationService,
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('FB_ID parameter is not set.');

        // Act
        $factory->create();
    }

    #[Test]
    #[TestDox('Odrzuca konfigurację, gdy FB_REDIRECT jest pusty lub nieprawidłowy')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidConfigValues')]
    public function itFailsWhenFbRedirectIsInvalid(mixed $invalidConfig): void
    {
        // /Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMapValueMethod(
                'has',
                [
                    ['FB_ID', true],
                    ['FB_SECRET', true],
                    ['FB_REDIRECT', true],
                ],
            ),
            new AllowedMapValueMethod(
                'get',
                [
                    ['FB_ID', 'id'],
                    ['FB_SECRET', 'secret'],
                    ['FB_REDIRECT', $invalidConfig],
                ],
            ),
        );
        $factory = new FBAuthenticatorFactory(
            $this->userRepository,
            $this->entityManager,
            $parameterBag,
            $this->registrationService,
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('FB_REDIRECT parameter is empty or invalid.');

        // Act
        $factory->create();
    }

    #[Test]
    #[TestDox('Odrzuca konfigurację, gdy FB_REDIRECT nie jest ustawione')]
    public function itFailsWhenFbRedirectIsMissing(): void
    {
        // Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMapValueMethod(
                'has',
                [
                    ['FB_ID', true],
                    ['FB_SECRET', true],
                    ['FB_REDIRECT', false],
                ],
            ),
        );
        $factory = new FBAuthenticatorFactory(
            $this->userRepository,
            $this->entityManager,
            $parameterBag,
            $this->registrationService,
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('FB_REDIRECT parameter is not set.');

        // Act
        $factory->create();
    }

    #[Test]
    #[TestDox('Odrzuca konfigurację, gdy FB_SECRET jest pusty lub nieprawidłowy')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidConfigValues')]
    public function itFailsWhenFbSecretIsInvalid(mixed $invalidConfig): void
    {
        // /Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMapValueMethod(
                'has',
                [
                    ['FB_ID', true],
                    ['FB_SECRET', true],
                    ['FB_REDIRECT', true],
                ],
            ),
            new AllowedMapValueMethod(
                'get',
                [
                    ['FB_ID', 'id'],
                    ['FB_SECRET', $invalidConfig],
                    ['FB_REDIRECT', 'url'],
                ],
            ),
        );
        $factory = new FBAuthenticatorFactory(
            $this->userRepository,
            $this->entityManager,
            $parameterBag,
            $this->registrationService,
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('FB_SECRET parameter is empty or invalid.');

        // Act
        $factory->create();
    }

    #[Test]
    #[TestDox('Odrzuca konfigurację, gdy FB_SECRET nie jest ustawione')]
    public function itFailsWhenFbSecretIsMissing(): void
    {
        // Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMapValueMethod(
                'has',
                [
                    ['FB_ID', true],
                    ['FB_SECRET', false],
                    ['FB_REDIRECT', true],
                ],
            ),
        );
        $factory = new FBAuthenticatorFactory(
            $this->userRepository,
            $this->entityManager,
            $parameterBag,
            $this->registrationService,
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('FB_SECRET parameter is not set.');

        // Act
        $factory->create();
    }
}
