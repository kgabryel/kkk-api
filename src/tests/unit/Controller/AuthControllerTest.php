<?php

namespace App\Tests\Unit\Controller;

use App\Controller\AuthController;
use App\Dto\Request\AuthToken;
use App\Entity\User;
use App\Factory\DtoFactoryDispatcher;
use App\Repository\UserRepository;
use App\Service\Auth\FBAuthenticator;
use App\Service\Auth\TokensService;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use App\Validation\FbLoginValidation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[Small]
#[CoversClass(AuthController::class)]
class AuthControllerTest extends BaseTestCase
{
    private AuthController $controller;
    private User $user;

    protected function setUp(): void
    {
        $this->user = EntityFactory::getSimpleUser();
        $dtoFactoryDispatcher = $this->getMock(DtoFactoryDispatcher::class);
        $userRepository = $this->getMock(UserRepository::class);
        $this->controller = new AuthController($dtoFactoryDispatcher, $userRepository);
    }

    #[Test]
    #[TestDox('Zwraca tokeny, gdy użytkownik już istnieje')]
    public function itAuthenticatesExistingUser(): void
    {
        // Arrange
        $auth = $this->getMock(
            FBAuthenticator::class,
            new AllowedMethod('getUserInfo', [FBAuthenticator::ID => 'fb-id'], $this->once()),
            new AllowedMethod('userExists', true, $this->once()),
            new AllowedMethod('getUser', $this->user, $this->once()),
        );
        $tokensService = $this->getMock(
            TokensService::class,
            new AllowedMethod(
                'getTokens',
                ['access_token' => 'xyz', 'refresh_token' => '123'],
                $this->once(),
                [$this->user],
            ),
        );
        $validation = $this->getMock(
            FbLoginValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $this->createStub(AuthToken::class)),
        );

        // Act
        $response = $this->controller->login($auth, $tokensService, $validation);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(json_encode(['access_token' => 'xyz', 'refresh_token' => '123']), $response->getContent());
    }

    #[Test]
    #[TestDox('Tworzy nowego użytkownika i zwraca tokeny, gdy użytkownik nie istnieje')]
    public function itCreatesAndAuthenticatesNewUser(): void
    {
        // Arrange
        $auth = $this->getMock(
            FBAuthenticator::class,
            new AllowedMethod('getUserInfo', [FBAuthenticator::ID => 'fb-id'], $this->once()),
            new AllowedMethod('userExists', false, $this->once()),
            new AllowedMethod('getUser', $this->user, $this->once()),
            new AllowedVoidMethod('createUser', $this->once()),
        );
        $tokensService = $this->getMock(
            TokensService::class,
            new AllowedMethod(
                'getTokens',
                ['access_token' => 'xyz', 'refresh_token' => '123'],
                $this->once(),
                [$this->user],
            ),
        );
        $validation = $this->getMock(
            FbLoginValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $this->createStub(AuthToken::class)),
        );

        // Act
        $response = $this->controller->login($auth, $tokensService, $validation);

        // Assert
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(json_encode(['access_token' => 'xyz', 'refresh_token' => '123']), $response->getContent());
    }

    #[Test]
    #[TestDox('Endpoint /api/facebook/redirect zwraca URL przekierowania')]
    public function itReturnsRedirectUrl(): void
    {
        // Arrange
        $auth = $this->getMock(
            FBAuthenticator::class,
            new AllowedMethod('getRedirectUrl', 'https://example.com/redirect', $this->once()),
        );

        // Act
        $response = $this->controller->getRedirectUrl($auth);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(
            json_encode([AuthController::URL => 'https://example.com/redirect']),
            $response->getContent(),
        );
    }

    #[Test]
    #[TestDox('Zwraca 401, gdy nie udało się pobrać informacji o użytkowniku z Facebooka')]
    public function itReturnsUnauthorizedWhenUserInfoFails(): void
    {
        // Arrange
        $auth = $this->getMock(
            FBAuthenticator::class,
            new AllowedMethod('getUserInfo', false, $this->once()),
        );
        $tokensService = $this->getMock(TokensService::class);
        $validation = $this->getMock(
            FbLoginValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(true), $this->once()),
            new AllowedMethod('getDto', $this->createStub(AuthToken::class)),
        );

        // Act
        $response = $this->controller->login($auth, $tokensService, $validation);

        // Assert
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca 401, gdy walidacja danych logowania nie przejdzie')]
    public function itReturnsUnauthorizedWhenValidationFails(): void
    {
        // Arrange
        $auth = $this->getMock(FBAuthenticator::class);
        $tokensService = $this->getMock(TokensService::class);
        $validation = $this->getMock(
            FbLoginValidation::class,
            new AllowedMethod('validate', $this->getValidationResult(false), $this->once()),
        );

        // Act
        $response = $this->controller->login($auth, $tokensService, $validation);

        // Assert
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
