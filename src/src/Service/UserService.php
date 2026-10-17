<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserService
{
    private TokenStorageInterface $tokenStorage;
    private UserRepository $userRepository;

    public function __construct(TokenStorageInterface $tokenStorage, UserRepository $userRepository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
    }

    public function getUser(): User
    {
        return $this->userRepository->find(
            $this->tokenStorage->getToken()?->getUser()?->getUserIdentifier() ?? 0,
        ) ?? new User();
    }
}
