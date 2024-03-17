<?php

namespace App\Validator\CorrectPassword;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraint;

class CorrectPassword extends Constraint
{
    private User $user;
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(User $user, UserPasswordEncoderInterface $passwordEncoder, array $options = [])
    {
        $this->user = $user;
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct($options);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPasswordEncoder(): UserPasswordEncoderInterface
    {
        return $this->passwordEncoder;
    }
}
