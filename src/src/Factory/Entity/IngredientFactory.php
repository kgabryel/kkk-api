<?php

namespace App\Factory\Entity;

use App\Entity\Ingredient;
use App\Validation\CreateIngredientValidation;

class IngredientFactory extends EntityFactory
{
    public function create(CreateIngredientValidation $ingredientValidation): ?Ingredient
    {
        if (!$ingredientValidation->validate()->passed()) {
            return null;
        }

        $data = $ingredientValidation->getDto();
        $ingredient = new Ingredient();
        $ingredient->setUser($this->user);
        $ingredient->setName($data->getName());
        $ingredient->setAvailable($data->isAvailable());
        $ingredient->setOzaId($data->getOzaId());
        $this->saveEntity($ingredient);

        return $ingredient;
    }
}
