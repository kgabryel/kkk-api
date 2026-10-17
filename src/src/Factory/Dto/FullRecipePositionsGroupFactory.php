<?php

namespace App\Factory\Dto;

use App\Dto\Entity\FullRecipePositionsGroup;
use App\Dto\Entity\List\FullRecipePositionList;
use App\Entity\RecipePositionGroup as RecipePositionGroupEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class FullRecipePositionsGroupFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): FullRecipePositionsGroup
    {
        if (!($entity instanceof RecipePositionGroupEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', RecipePositionGroupEntity::class),
            );
        }

        return new FullRecipePositionsGroup(
            $entity->getName(),
            $dtoFactoryDispatcher->getMany(FullRecipePositionList::class, ...$entity->getRecipePositions()),
        );
    }

    public function getDtoName(): string
    {
        return FullRecipePositionsGroup::class;
    }
}
