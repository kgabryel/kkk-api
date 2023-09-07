<?php

namespace App\Service\Entity;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ApiKeyService extends EntityService
{
    private ApiKey $apiKey;
    private ApiKeyRepository $apiKeyRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        ApiKeyRepository $apiKeyRepository
    ) {
        parent::__construct($entityManager, $tokenStorage);
        $this->apiKeyRepository = $apiKeyRepository;
    }

    public function find(int $id): bool
    {
        $apiKey = $this->apiKeyRepository->findById($id, $this->user);
        if ($apiKey === null) {
            return false;
        }
        $this->apiKey = $apiKey;

        return true;
    }

    public function switch(): void
    {
        $this->apiKey->isActive() ? $this->apiKey->deactivate() : $this->apiKey->activate();
        $this->saveEntity($this->apiKey);
    }

    public function remove(): void
    {
        $this->removeEntity($this->apiKey);
    }

    public function getKey(): ApiKey
    {
        return $this->apiKey;
    }
}
