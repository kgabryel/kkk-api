<?php

namespace App\Factory\Dto;

use App\Dto\Entity\FullRecipePosition;
use App\Entity\RecipePosition as RecipePositionEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class FullRecipePositionFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): FullRecipePosition
    {
        if (!($entity instanceof RecipePositionEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', RecipePositionEntity::class),
            );
        }

        return new FullRecipePosition(
            $entity->getAmount(),
            $entity->getMeasure(),
            $entity->isAdditional(),
            $entity->getIngredient()?->getName(),
            $entity->getRecipe()?->getName(),
        );
    }

    public function getDtoName(): string
    {
        return FullRecipePosition::class;
    }
}
