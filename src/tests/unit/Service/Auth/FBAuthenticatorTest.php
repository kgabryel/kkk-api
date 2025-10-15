<?php

namespace App\Tests\Unit\Service\Auth;

use App\Entity\Settings;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Auth\FBAuthenticator;
use App\Service\Auth\RegistrationService;
use App\Tests\Helper\AllowedMethod\AllowedCallbackMethod;
use App\Tests\Helper\AllowedMethod\AllowedExceptionMethod;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(FBAuthenticator::class)]
class FBAuthenticatorTest extends BaseTestCase
{
    private FBAuthenticator $FBAuthenticator;

    #[Test]
    #[TestDox('Tworzy nowego użytkownika z danymi z Facebooka')]
    public function itCreatesUserWithProperData(): void
    {
        // Arrange
        $persisted = [];
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('flush', $this->once()),
            new AllowedCallbackMethod(
                'persist',
                function ($entity) use (&$persisted): void {
                    $persisted[] = $entity;
                },
                $this->exactly(2),
            ),
        );
        $registrationService = $this->getMock(
            RegistrationService::class,
            new AllowedMethod('getSettings', EntityFactory::getSimpleSettings(), $this->once()),
        );
        $this->init(entityManager: $entityManager, registrationService: $registrationService);
        $userInfo = ['id' => '123456'];

        // Act
        $user = $this->FBAuthenticator->createUser($userInfo);

        // Prepare expected
        $classes = array_map(static fn ($object): string => get_class($object), $persisted);

        // Assert
        $this->assertSame($userInfo['id'], $user->getFbId());
        $this->assertSame('123456@fb.com', $user->getEmail());
        $this->assertSame(30, strlen($user->getPassword()));
        $this->assertCount(2, $persisted);
        $this->assertContains(Settings::class, $classes);
        $this->assertContains(User::class, $classes);
    }

    #[Test]
    #[TestDox('Tworzy nowego użytkownika z danymi z Facebooka')]
    public function itReturnsFalseOnException(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                Facebook::class,
                new AllowedExceptionMethod('getAccessToken', $this->once(), new Exception('FB error')),
            ),
        );

        // Act
        $result = $this->FBAuthenticator->getUserInfo('invalid-token');

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox('Zwraca false, gdy użytkownik z danym fbId nie istnieje')]
    public function itReturnsFalseWhenUserNotFound(): void
    {
        // Arrange
        $this->init(
            userRepository: $this->getMock(
                UserRepository::class,
                new AllowedMethod('findOneBy', null, $this->once(), [['fbId' => '987654321']]),
            ),
        );

        // Act
        $result = $this->FBAuthenticator->userExists('987654321');

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[TestDox('Zwraca URL przekierowania do Facebooka')]
    public function itReturnsRedirectUrl(): void
    {
        // Arrange
        $this->init(
            $this->getMock(
                Facebook::class,
                new AllowedMethod('getAuthorizationUrl', 'https://www.facebook.com/mock', $this->once()),
            ),
        );

        // Act
        $url = $this->FBAuthenticator->getRedirectUrl();

        // Assert
        $this->assertSame('https://www.facebook.com/mock', $url);
    }

    #[Test]
    #[TestDox('Zwraca true, gdy użytkownik z danym fbId istnieje')]
    public function itReturnsTrueWhenUserExists(): void
    {
        // Arrange
        $this->init(
            userRepository: $this->getMock(
                UserRepository::class,
                new AllowedMethod(
                    'findOneBy',
                    EntityFactory::getSimpleUser(),
                    $this->once(),
                    [['fbId' => '987654321']],
                ),
            ),
        );

        // Act
        $result = $this->FBAuthenticator->userExists('987654321');

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox('Zwraca dane użytkownika z Facebooka w formie tablicy')]
    public function itReturnsUserDataOnSuccess(): void
    {
        // Arrange
        $accessToken = $this->getMock(AccessToken::class);
        $resourceOwnerMock = $this->getMock(
            ResourceOwnerInterface::class,
            new AllowedMethod('toArray', ['id' => '12345', 'name' => 'Test User'], $this->once()),
        );
        $facebook = $this->getMock(
            Facebook::class,
            new AllowedMethod(
                'getAccessToken',
                $accessToken,
                $this->once(),
                ['authorization_code', ['code' => 'fake-token']],
            ),
            new AllowedMethod('getResourceOwner', $resourceOwnerMock, $this->once(), [$accessToken]),
        );
        $this->init($facebook);

        // Act
        $result = $this->FBAuthenticator->getUserInfo('fake-token');

        // Assert
        $this->assertIsArray($result);
        $this->assertSame(['id' => '12345', 'name' => 'Test User'], $result);
    }

    private function init(
        ?Facebook $facebook = null,
        ?UserRepository $userRepository = null,
        ?EntityManagerInterface $entityManager = null,
        ?RegistrationService $registrationService = null,
    ): void {
        $this->FBAuthenticator = new FBAuthenticator(
            $facebook ?? $this->getMock(Facebook::class),
            $userRepository ?? $this->getMock(UserRepository::class),
            $entityManager ?? $this->getMock(EntityManagerInterface::class),
            $registrationService ?? $this->getMock(RegistrationService::class),
        );
    }
}
