<?php

namespace App\Tests\Functional;

use App\Controller\AuthController;
use App\Tests\Helper\TestCase\BaseFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Response;

#[Large]
#[CoversClass(AuthController::class)]
class AuthControllerTest extends BaseFunctionalTestCase
{
    #[Test]
    #[TestDox('Umożliwia wywołanie endpointu /api/facebook/login')]
    public function itRespondsOnLoginEndpoint(): void
    {
        // Act
        $this->client->request('POST', '/api/facebook/login');

        // Assert
        self::assertNotSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    #[Test]
    #[TestDox('Endpoint /api/facebook/redirect zwraca URL przekierowania')]
    public function itReturnsRedirectUrl(): void
    {
        // Act
        $this->client->request('GET', '/api/facebook/redirect');

        // Prepare expected
        $content = $this->getResponseContent();

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertArrayHasKey('url', $content);
        $this->assertIsString($content['url']);
    }
}
