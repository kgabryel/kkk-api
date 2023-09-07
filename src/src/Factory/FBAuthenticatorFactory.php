<?php

namespace App\Factory;

use App\Repository\UserRepository;
use App\Service\Auth\FBAuthenticator;
use App\Service\Auth\FBFactory;
use Doctrine\ORM\EntityManagerInterface;

class FBAuthenticatorFactory
{
    private UserRepository $userRepository;
    private EntityManagerInterface $manager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $manager)
    {
        $this->userRepository = $userRepository;
        $this->manager = $manager;
    }

    public function create(): FBAuthenticator
    {
        return new FBAuthenticator(
            FBFactory::getInstance($_ENV['FB_REDIRECT']),
            $this->userRepository,
            $this->manager
        );
    }
}
