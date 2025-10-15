<?php

namespace App\Dto\Request;

use App\Entity\User as UserEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class User
{
    private string $email;
    private string $password;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher, string $email, string $password)
    {
        $this->passwordHasher = $passwordHasher;
        $this->email = $email;
        $this->password = $password;
    }

    public function getUser(): UserEntity
    {
        $user = new UserEntity();
        $user->setEmail($this->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $this->password));

        return $user;
    }
}
