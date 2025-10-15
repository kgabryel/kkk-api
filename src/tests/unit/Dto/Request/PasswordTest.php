<?php

namespace App\Tests\Unit\Dto\Request;

use App\Dto\Request\Password;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Small]
#[CoversClass(Password::class)]
class PasswordTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Zwraca hash hasła, wygenerowany dla przekazanego usera')]
    public function itShouldReturnPasswordHashForGivenUser(): void
    {
        // Arrange
        $user = EntityFactory::getSimpleUser();
        $passwordHasher = $this->getMock(
            UserPasswordHasherInterface::class,
            new AllowedMethod('hashPassword', 'hash-password', $this->once(), [$user, 'password']),
        );
        $passwordDto = new Password($passwordHasher, $user, 'password');

        // Act
        $actualHash = $passwordDto->getPassword();

        // Assert
        $this->assertSame('hash-password', $actualHash);
    }
}
