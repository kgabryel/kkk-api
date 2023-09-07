<?php

namespace App\Service\Auth;

use App\Model\User;
use App\Service\Entity\SettingsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationService
{
    private EntityManagerInterface $manager;
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(EntityManagerInterface $manager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->manager = $manager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function register(User $model): void
    {
        $user = $model->getUser($this->passwordEncoder);
        $settings = SettingsService::get($user);
        $this->manager->persist($settings);
        $this->manager->persist($user);
        $this->manager->flush();
    }
}
