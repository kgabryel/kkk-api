<?php

namespace App\Service\Entity;

use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use App\Service\UserService;
use App\Validation\EditIngredientValidation;
use Doctrine\ORM\EntityManagerInterface;

class IngredientService extends EntityService
{
    private IngredientRepository $ingredientRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        IngredientRepository $ingredientRepository,
    ) {
        parent::__construct($entityManager, $userService);
        $this->ingredientRepository = $ingredientRepository;
    }

    public function clearOzaKeys(): void
    {
        $this->ingredientRepository->resetOzaIdsForUser($this->user);
    }

    public function disconnectFromOZA(Ingredient $ingredient): void
    {
        $ingredient->setOzaId(null);
        $this->saveEntity($ingredient);
    }

    public function find(int $id): ?Ingredient
    {
        return $this->ingredientRepository->findById($id, $this->user);
    }

    public function findByOzaId(int $id): ?Ingredient
    {
        return $this->ingredientRepository->findOneBy([
            'ozaId' => $id,
            'user' => $this->user,
        ]);
    }

    public function getFirstIngredientWithOza(): ?Ingredient
    {
        return $this->ingredientRepository->findFirstIngredientWithOza($this->user);
    }

    public function remove(Ingredient $ingredient): void
    {
        $this->removeEntity($ingredient);
    }

    public function update(Ingredient $ingredient, EditIngredientValidation $ingredientValidation): bool
    {
        $ingredientValidation->setExpect($ingredient->getId());
        if (!$ingredientValidation->validate()->passed()) {
            return false;
        }

        $data = $ingredientValidation->getDto();
        $name = $data->getName();
        $available = $data->isAvailable();
        $ozaId = $data->getOzaId();
        if ($name !== null) {
            $ingredient->setName($name);
        }

        if ($available !== null) {
            $ingredient->setAvailable($available);
            $ingredient->setOzaId(null);
        } elseif ($ozaId !== null) {
            if ($ozaId === 0) {
                $ozaId = null;
            }
            $ingredient->setOzaId($ozaId);
        }

        $this->saveEntity($ingredient);

        return true;
    }

    public function updateAvailable(Ingredient $ingredient, bool $available): void
    {
        $ingredient->setAvailable($available);
        $this->saveEntity($ingredient);
    }
}
