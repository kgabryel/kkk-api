<?php

namespace App\Service\Auth;

use App\Entity\Settings;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationService
{
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function getSettings(User $user): Settings
    {
        $settings = new Settings();
        $settings->setAutocomplete(true);
        $settings->setOzaKey(null);
        $settings->setUser($user);

        return $settings;
    }

    public function register(User $user): void
    {
        $settings = $this->getSettings($user);
        $this->manager->persist($settings);
        $this->manager->persist($user);
        $this->manager->flush();
    }
}
