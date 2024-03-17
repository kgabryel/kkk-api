<?php

namespace App\Dto;

use App\Entity\RecipePosition as RecipePositionEntity;
use App\Entity\RecipePositionGroup as Entity;
use InvalidArgumentException;

class RecipePositionsGroup implements DtoInterface
{
    protected string $name;
    /** @var RecipePosition[] */
    protected array $positions;

    /**
     * @param  string  $name
     * @param  RecipePositionEntity[]  $positions
     */
    public function __construct(string $name, array $positions)
    {
        $this->name = $name;
        $this->positions = array_map(
            static fn(RecipePositionEntity $position): RecipePosition => RecipePosition::createFromEntity($position),
            $positions
        );
    }

    /**
     * @param  Entity  $entity
     *
     * @return self
     */
    public static function createFromEntity(mixed $entity): self
    {
        if (!($entity instanceof Entity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', Entity::class)
            );
        }

        return new self($entity->getName(), array_values($entity->getRecipePosition()->toArray()));
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return RecipePosition[]
     */
    public function getPositions(): array
    {
        return $this->positions;
    }
}
