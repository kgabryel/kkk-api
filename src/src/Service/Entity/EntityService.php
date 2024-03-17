<?php

namespace App\Service\Entity;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;

abstract class EntityService
{
    protected EntityManagerInterface $entityManager;
    protected User $user;

    public function __construct(EntityManagerInterface $entityManager, UserService $userService)
    {
        $this->entityManager = $entityManager;
        $this->user = $userService->getUser();
    }

    abstract public function find(int $id): bool;

    protected function saveEntity(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    protected function removeEntity(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}
