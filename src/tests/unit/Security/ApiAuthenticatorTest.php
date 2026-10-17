<?php

namespace App\Tests\Unit\Security;

use App\Repository\ApiKeyRepository;
use App\Security\ApiAuthenticator;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

#[Small]
#[CoversClass(ApiAuthenticator::class)]
class ApiAuthenticatorTest extends BaseTestCase
{
    private ApiAuthenticator $authenticator;
    private ApiKeyRepository $repository;
    private Request $request;

    protected function setUp(): void
    {
        $this->repository = $this->getMock(ApiKeyRepository::class);
        $this->authenticator = new ApiAuthenticator($this->repository);
        $this->request = $this->createStub(Request::class);
    }

    #[Test]
    #[TestDox('Zwraca użytkownika dla poprawnego klucza API')]
    public function itFindsUserByApiKey(): void
    {
        // Arrange
        $apiKey = EntityFactory::getSimpleApiKey();
        $apiKey->setUser(EntityFactory::getSimpleUser());
        $headers = $this->getMock(
            HeaderBag::class,
            new AllowedMethod('get', 'key', $this->once(), ['X-AUTH-TOKEN']),
        );
        $this->request->headers = $headers;
        $this->repository = $this->getMock(
            ApiKeyRepository::class,
            new AllowedMethod('findOneBy', $apiKey, $this->once(), [['key' => 'key', 'active' => true]]),
        );
        $this->authenticator = new ApiAuthenticator($this->repository);

        // Act
        $this->authenticator->authenticate($this->request);
    }

    #[Test]
    #[TestDox('Nie zwraca odpowiedzi po udanej autoryzacji')]
    public function itReturnsNullOnSuccessfulAuthentication(): void
    {
        // Arrange
        $token = $this->createStub(TokenInterface::class);

        // Act
        $response = $this->authenticator->onAuthenticationSuccess($this->request, $token, '');

        // Assert
        $this->assertNull($response);
    }

    #[Test]
    #[TestDox('Zwraca 401 przy błędzie autoryzacji')]
    public function itReturnsUnauthorizedOnAuthFailure(): void
    {
        // Arrange
        $exception = $this->createStub(AuthenticationException::class);

        // Act
        $response = $this->authenticator->onAuthenticationFailure($this->request, $exception);

        // Assert
        $this->assertSame(401, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Obsługuje każde żądanie')]
    public function itSupportsAlways(): void
    {
        // Act
        $result = $this->authenticator->supports($this->request);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek przy braku nagłówka X-AUTH-TOKEN')]
    public function itThrowsExceptionWhenAuthTokenMissing(): void
    {
        // Arrange
        $headers = $this->getMock(
            HeaderBag::class,
            new AllowedMethod('get', null, $this->once(), ['X-AUTH-TOKEN']),
        );
        $this->request->headers = $headers;

        // Assert
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Token missing.');

        // Act
        $this->authenticator->authenticate($this->request);
    }
}
