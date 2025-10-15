<?php

namespace App\Tests\Unit\Dto\Request;

use App\Dto\Request\User;
use App\Entity\User as UserEntity;
use App\Tests\Helper\AllowedMethod\AllowedMethod;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\TestCase\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Small]
#[CoversClass(User::class)]
class UserTest extends BaseTestCase
{
    #[Test]
    #[TestDox('Tworzy użytkownika z e-mailem i hashem hasła')]
    public function itCreatesUserWithEmailAndHashedPassword(): void
    {
        // Arrange
        $passwordHasher = $this->getMock(
            UserPasswordHasherInterface::class,
            new AllowedMethod(
                'hashPassword',
                'hash-password',
                $this->once(),
                [$this->isInstanceOf(UserEntity::class), 'password'],
            ),
        );
        $dto = new User($passwordHasher, EntityFactory::USER_EMAIL, 'password');

        // Act
        $user = $dto->getUser();

        // Assert
        $this->assertSame(EntityFactory::USER_EMAIL, $user->getEmail());
        $this->assertSame('hash-password', $user->getPassword());
    }
}
