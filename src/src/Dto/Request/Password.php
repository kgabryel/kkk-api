<?php

namespace App\Dto\Request;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class Password
{
    private string $password;

    public function __construct(UserPasswordHasherInterface $passwordHasher, User $user, string $password)
    {
        $this->password = $passwordHasher->hashPassword($user, $password);
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
