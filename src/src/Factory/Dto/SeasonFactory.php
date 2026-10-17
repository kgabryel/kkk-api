<?php

namespace App\Factory\Dto;

use App\Dto\Entity\Season;
use App\Entity\Season as SeasonEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class SeasonFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): Season
    {
        if (!($entity instanceof SeasonEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', SeasonEntity::class),
            );
        }

        return new Season(
            $entity->getId(),
            $entity->getIngredient()->getId(),
            $entity->getStart(),
            $entity->getStop(),
        );
    }

    public function getDtoName(): string
    {
        return Season::class;
    }
}
