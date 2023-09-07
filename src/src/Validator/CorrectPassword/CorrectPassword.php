<?php

namespace App\Validator\CorrectPassword;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraint;

class CorrectPassword extends Constraint
{
    public const PASSWORD_ENCODER_OPTION = 'passwordEncoder';
    public const USER_OPTION = 'user';
    private User $user;
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(array $options = [])
    {
        $this->user = $options[self::USER_OPTION];
        $this->passwordEncoder = $options[self::PASSWORD_ENCODER_OPTION];

        parent::__construct(self::clearOptionsArray($options));
    }

    private static function clearOptionsArray(array $options): array
    {
        unset($options[self::USER_OPTION], $options[self::PASSWORD_ENCODER_OPTION]);

        return $options;
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
