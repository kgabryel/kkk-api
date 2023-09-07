<?php

namespace App\Dto;

use App\Entity\RecipePosition as PositionEntity;
use App\Entity\RecipePositionGroup as Entity;
use InvalidArgumentException;

class FullRecipePositionsGroup extends RecipePositionsGroup
{
    /**
     * RecipePositionsGroup constructor.
     *
     * @param  string  $name
     * @param  PositionEntity[]  $positions
     */
    public function __construct(string $name, array $positions)
    {
        parent::__construct($name, $positions);
        $this->positions = array_map(
            static fn(PositionEntity $position): FullRecipePosition => FullRecipePosition::createFromEntity($position),
            $positions
        );
    }

    /**
     * @param  Entity  $entity
     *
     * @return self
     */
    public static function createFromEntity($entity): self
    {
        if (!($entity instanceof Entity)) {
            throw new InvalidArgumentException(
                printf('Parameter "entity" isn\'t an instance of "%s" class', Entity::class)
            );
        }

        return new self($entity->getName(), array_values($entity->getRecipePosition()->toArray()));
    }
}
