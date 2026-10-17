<?php

namespace App\Tests\Unit\Service\Auth;

use App\Service\Auth\TokensService;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(TokensService::class)]
class TokenServiceTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Generuje access i refresh token oraz zapisuje refresh token')]
    public function itReturnsAccessAndRefreshToken(): void
    {
        // Arrange
        $user = EntityFactory::getSimpleUser();
        $refreshToken = $this->getMock(
            RefreshTokenInterface::class,
            new AllowedVoidMethod('setUsername', $this->once(), [$user->getUserIdentifier()]),
            new AllowedVoidMethod('setRefreshToken', $this->once()),
            new AllowedMethod('getRefreshToken', 'refresh_token', $this->once()),
        );
        $tokenManager = $this->getMock(
            JWTTokenManagerInterface::class,
            new AllowedMethod('create', 'access_token', $this->once(), [$user]),
        );
        $tokenGenerator = $this->getMock(
            RefreshTokenGeneratorInterface::class,
            new AllowedMethod('createForUserWithTtl', $refreshToken, $this->once(), [$user, 2592000]),
        );
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('persist', $this->once(), [$refreshToken]),
            new AllowedVoidMethod('flush', $this->once()),
        );
        $tokenService = new TokensService($tokenManager, $tokenGenerator, $entityManager);

        // Act
        $tokens = $tokenService->getTokens($user);

        // Assert
        $this->assertArrayHasKey('token', $tokens);
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertSame('refresh_token', $tokens['refresh_token']);
        $this->assertSame('access_token', $tokens['token']);
    }
}
