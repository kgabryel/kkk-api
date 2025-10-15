<?php

namespace App\Service\Entity;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;

class ApiKeyService extends EntityService
{
    private ApiKeyRepository $apiKeyRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        ApiKeyRepository $apiKeyRepository,
    ) {
        parent::__construct($entityManager, $userService);
        $this->apiKeyRepository = $apiKeyRepository;
    }

    public function find(int $id): ?ApiKey
    {
        return $this->apiKeyRepository->findById($id, $this->user);
    }

    public function remove(ApiKey $apiKey): void
    {
        $this->removeEntity($apiKey);
    }

    public function switch(ApiKey $apiKey): void
    {
        $apiKey->isActive() ? $apiKey->deactivate() : $apiKey->activate();
        $this->saveEntity($apiKey);
    }
}
