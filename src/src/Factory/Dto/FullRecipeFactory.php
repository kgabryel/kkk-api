<?php

namespace App\Factory\Dto;

use App\Dto\Entity\FullRecipe;
use App\Dto\Helper\RecipeRelatedEntities;
use App\Dto\List\Entity\PhotoList;
use App\Dto\List\Entity\RecipePositionGroupList;
use App\Dto\List\Entity\TagList;
use App\Dto\List\Entity\TimerList;
use App\Entity\Recipe as RecipeEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class FullRecipeFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): FullRecipe
    {
        if (!($entity instanceof RecipeEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', RecipeEntity::class),
            );
        }

        return new FullRecipe(
            $entity->getId(),
            $entity->getName(),
            $entity->getDescription(),
            $entity->getUrl(),
            $entity->getPortions(),
            new RecipeRelatedEntities(
                new TagList(...$entity->getTags()),
                new RecipePositionGroupList(...$entity->getRecipePositionGroups()),
                new TimerList(),
                new PhotoList(...$entity->getPhotos()),
            ),
            $dtoFactoryDispatcher,
        );
    }

    public function getDtoName(): string
    {
        return FullRecipe::class;
    }
}
