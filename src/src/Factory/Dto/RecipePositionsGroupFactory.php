<?php

namespace App\Factory\Dto;

use App\Dto\Entity\List\RecipePositionList;
use App\Dto\Entity\RecipePositionsGroup;
use App\Entity\RecipePositionGroup as RecipePositionGroupEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class RecipePositionsGroupFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): RecipePositionsGroup
    {
        if (!($entity instanceof RecipePositionGroupEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', RecipePositionGroupEntity::class),
            );
        }

        return new RecipePositionsGroup(
            $entity->getName(),
            $dtoFactoryDispatcher->getMany(RecipePositionList::class, ...$entity->getRecipePositions()),
        );
    }

    public function getDtoName(): string
    {
        return RecipePositionsGroup::class;
    }
}
