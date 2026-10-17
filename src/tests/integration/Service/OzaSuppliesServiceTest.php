<?php

namespace App\Tests\Integration\Service;

use App\Entity\Settings;
use App\Entity\User;
use App\Service\OzaSuppliesService;
use App\Service\UserService;
use App\Tests\DataProvider\OzaSupplyDataProvider;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[Medium]
#[CoversClass(OzaSuppliesService::class)]
class OzaSuppliesServiceTest extends BaseIntegrationTestCase
{
    #[Test]
    #[TestDox('Zwraca dane pobrane z API')]
    #[DataProviderExternal(OzaSupplyDataProvider::class, 'responseValues')]
    public function itReturnsDataFromApi(string $responseData, array $expectedData): void
    {
        // Arrange
        $mockResponse = new MockResponse(
            $responseData,
            [
                'http_code' => 200,
                'response_headers' => ['Content-Type' => 'application/json'],
            ],
        );
        $client = new MockHttpClient($mockResponse);
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
        $parameterBagInterface = $this->getMock(
            ParameterBagInterface::class,
            new AllowedMethod('has', true, $this->once(), ['OZA_URL']),
            new AllowedMethod('get', 'uza_url', $this->once(), ['OZA_URL']),
        );
        $ozaSuppliesService = new OzaSuppliesService($userService, $client, $parameterBagInterface);
        $result = $ozaSuppliesService->downloadSupplies();

        // Act
        $supplies = $ozaSuppliesService->getSupplies();

        // Assert
        $this->assertEquals($expectedData, $supplies);
        $this->assertTrue($result);
    }
}
