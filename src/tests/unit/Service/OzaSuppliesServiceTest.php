<?php

namespace App\Tests\Unit\Service;

use App\Entity\Settings;
use App\Entity\User;
use App\Service\OzaSuppliesService;
use App\Service\UserService;
use App\Tests\DataProvider\CommonDataProvider;
use App\Tests\DataProvider\OzaSupplyDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedExceptionMethod;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\TestCase\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[Small]
#[CoversClass(OzaSuppliesService::class)]
class OzaSuppliesServiceTest extends BaseTestCase
{
    private OzaSuppliesService $ozaSuppliesService;

    #[Test]
    #[TestDox('Rzuca wyjątek, gdy brakuje konfiguracji OZA_URL')]
    public function itFailsWhenOzaUrlIsMissing(): void
    {
        // Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', false, $this->once(), ['OZA_URL']),
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('OZA_URL parameter is not set.');

        // Act
        $this->init(parameterBag: $parameterBag);
    }

    #[Test]
    #[TestDox('Rzuca wyjątek, gdy brakuje konfiguracji OZA_URL')]
    public function itRejectsDownloadWhenUserKeyIsMissing(): void
    {
        // Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', true, $this->once(), ['OZA_URL']),
            new AllowedMethod('get', 'oza-url', $this->once(), ['OZA_URL']),
        );
        $settings = $this->getMock(
            Settings::class,
            new AllowedMethod('getOzaKey', null, $this->once()),
        );
        $user = $this->getMock(
            User::class,
            new AllowedMethod('getSettings', $settings, $this->once()),
        );
        $userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $user, $this->once()),
        );
        $this->init($userService, parameterBag: $parameterBag);

        // Act
        $result = $this->ozaSuppliesService->downloadSupplies();

        // Assert
        $this->assertFalse($result);
        $this->assertSame(403, $this->ozaSuppliesService->getErrorStatusCode());
    }

    #[Test]
    #[TestDox('Zwraca false i kod błędu, gdy pobieranie danych się nie powiedzie')]
    #[DataProviderExternal(OzaSupplyDataProvider::class, 'errorStatusCodesValues')]
    public function itReturnsFalseAndSetsErrorCodeOnFailure(int $statusCode, int $expectedCode): void
    {
        // Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', true, $this->once(), ['OZA_URL']),
            new AllowedMethod('get', 'oza-url', $this->once(), ['OZA_URL']),
        );
        $settings = $this->getMock(
            Settings::class,
            new AllowedMethod('getOzaKey', 'OZA_KEY', $this->once()),
        );
        $user = $this->getMock(
            User::class,
            new AllowedMethod('getSettings', $settings, $this->once()),
        );
        $userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $user, $this->once()),
        );
        $exception = $this->createStub(ClientException::class);
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $exception->method('getResponse')->willReturn($response);
        $httpClient = $this->getMock(
            HttpClientInterface::class,
            new AllowedExceptionMethod('request', $this->once(), $exception),
        );
        $this->init($userService, $httpClient, $parameterBag);

        // Act
        $result = $this->ozaSuppliesService->downloadSupplies();

        // Assert
        $this->assertFalse($result);
        $this->assertSame($expectedCode, $this->ozaSuppliesService->getErrorStatusCode());
    }

    #[Test]
    #[TestDox('Rzuca wyjątek, gdy OZA_URL jest pusty lub niepoprawny')]
    #[DataProviderExternal(CommonDataProvider::class, 'invalidConfigValues')]
    public function itThrowsWhenFrontUrlIsInvalid(mixed $ozaUrl): void
    {
        // Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', true, $this->once(), ['OZA_URL']),
            new AllowedMethod('get', $ozaUrl, $this->once(), ['OZA_URL']),
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('OZA_URL parameter is empty or invalid.');

        // Act
        $this->init(parameterBag: $parameterBag);
    }

    #[Test]
    #[TestDox('Używa OzaKey z ustawień użytkownika')]
    public function itUseUserOzaKey(): void
    {
        // Arrange
        $parameterBag = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', true, $this->once(), ['OZA_URL']),
            new AllowedMethod('get', 'oza-url', $this->once(), ['OZA_URL']),
        );
        $settings = $this->getMock(
            Settings::class,
            new AllowedMethod('getOzaKey', 'OZA_KEY', $this->once()),
        );
        $user = $this->getMock(
            User::class,
            new AllowedMethod('getSettings', $settings, $this->once()),
        );
        $userService = $this->getMock(
            UserService::class,
            new AllowedMethod('getUser', $user, $this->once()),
        );

        // Act
        $this->init($userService, parameterBag: $parameterBag);
    }

    private function init(
        ?UserService $userService = null,
        ?HttpClientInterface $httpClient = null,
        ?ParameterBagInterface $parameterBag = null,
    ): void {
        $this->ozaSuppliesService = new OzaSuppliesService(
            $userService ?? $this->createStub(UserService::class),
            $httpClient ?? $this->getMock(HttpClientInterface::class),
            $parameterBag ?? $this->getMock(ParameterBagInterface::class),
        );
    }
}
