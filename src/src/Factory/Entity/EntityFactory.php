<?php

namespace App\Factory\Entity;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;

abstract class EntityFactory
{
    protected EntityManagerInterface $entityManager;
    protected User $user;

    public function __construct(EntityManagerInterface $entityManager, UserService $userService)
    {
        $this->entityManager = $entityManager;
        $this->user = $userService->getUser();
    }

    protected function saveEntity(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
