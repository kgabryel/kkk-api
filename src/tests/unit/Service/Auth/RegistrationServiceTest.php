<?php

namespace App\Tests\Unit\Service\Auth;

use App\Entity\User;
use App\Service\Auth\RegistrationService;
use App\Tests\Helper\AllowedMethod\AllowedCallbackMethod;
use App\Tests\Helper\AllowedMethod\AllowedVoidMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Small]
#[CoversClass(RegistrationService::class)]
class RegistrationServiceTest extends BaseTestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = EntityFactory::getSimpleUser();
    }

    #[Test]
    #[TestDox('Podczas rejestracji użytkownik i jego ustawienia są zapisywane w bazie')]
    public function itPersistsUserAndSettings(): void
    {
        // Arrange
        $persisted = [];
        $entityManager = $this->getMock(
            EntityManagerInterface::class,
            new AllowedVoidMethod('flush', $this->once()),
            new AllowedCallbackMethod(
                'persist',
                function ($arg) use (&$persisted): void {
                    $persisted[] = $arg;
                },
                $this->exactly(2),
            ),
        );
        $service = new RegistrationService($entityManager);

        // Act
        $service->register($this->user);

        // Assert
        $this->assertCount(2, $persisted);
        $this->assertSame($this->user, $persisted[1]);
    }

    #[Test]
    #[TestDox('Zwracane są poprawne ustawienia domyślne')]
    public function itReturnsSettings(): void
    {
        // Arrange
        $entityManager = $this->getMock(EntityManagerInterface::class);
        $service = new RegistrationService($entityManager);

        // Act
        $settings = $service->getSettings($this->user);

        // Assert
        $this->assertTrue($settings->getAutocomplete());
        $this->assertNull($settings->getOzaKey());
        $this->assertSame($this->user, $settings->getUser());
    }
}
