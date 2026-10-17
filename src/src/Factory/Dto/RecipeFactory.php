<?php

namespace App\Factory\Dto;

use App\Dto\Entity\Recipe;
use App\Dto\Helper\RecipeFlags;
use App\Dto\Helper\RecipeRelatedEntities;
use App\Dto\List\Entity\PhotoList;
use App\Dto\List\Entity\RecipePositionGroupList;
use App\Dto\List\Entity\TagList;
use App\Dto\List\Entity\TimerList;
use App\Entity\Recipe as RecipeEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class RecipeFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): Recipe
    {
        if (!($entity instanceof RecipeEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', RecipeEntity::class),
            );
        }

        return new Recipe(
            $entity->getId(),
            $entity->getName(),
            $entity->getDescription(),
            $entity->getUrl(),
            $entity->getPortions(),
            $entity->getPublicId(),
            new RecipeFlags($entity->isFavourite(), $entity->isToDo(), $entity->isPublic()),
            new RecipeRelatedEntities(
                new TagList(...$entity->getTags()),
                new RecipePositionGroupList(...$entity->getRecipePositionGroups()),
                new TimerList(...$entity->getTimers()),
                new PhotoList(...$entity->getPhotos()),
            ),
            $dtoFactoryDispatcher,
        );
    }

    public function getDtoName(): string
    {
        return Recipe::class;
    }
}
