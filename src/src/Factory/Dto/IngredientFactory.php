<?php

namespace App\Factory\Dto;

use App\Dto\Entity\Ingredient;
use App\Entity\Ingredient as IngredientEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class IngredientFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): Ingredient
    {
        if (!($entity instanceof IngredientEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', IngredientEntity::class),
            );
        }

        return new Ingredient(
            $entity->getId(),
            $entity->getName(),
            $entity->isAvailable(),
            $entity->getOzaId(),
        );
    }

    public function getDtoName(): string
    {
        return Ingredient::class;
    }
}
