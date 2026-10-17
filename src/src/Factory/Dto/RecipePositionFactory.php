<?php

namespace App\Factory\Dto;

use App\Dto\Entity\RecipePosition;
use App\Entity\RecipePosition as RecipePositionEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class RecipePositionFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): RecipePosition
    {
        if (!($entity instanceof RecipePositionEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', RecipePositionEntity::class),
            );
        }

        return new RecipePosition(
            $entity->getAmount(),
            $entity->getMeasure(),
            $entity->getIngredient()?->getId(),
            $entity->getRecipe()?->getId(),
            $entity->isAdditional(),
        );
    }

    public function getDtoName(): string
    {
        return RecipePosition::class;
    }
}
