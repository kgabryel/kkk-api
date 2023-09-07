<?php

namespace App\Dto;

use App\Entity\RecipePosition as Entity;
use InvalidArgumentException;

class RecipePosition implements DtoInterface
{
    private ?float $amount;
    private string $measure;
    private ?int $ingredient;
    private ?int $recipe;
    private bool $additional;

    public function __construct(
        ?float $amount,
        string $measure,
        ?int $ingredient,
        ?int $recipe,
        bool $additional
    ) {
        $this->amount = $amount;
        $this->measure = $measure;
        $this->ingredient = $ingredient;
        $this->recipe = $recipe;
        $this->additional = $additional;
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

        return new self(
            $entity->getAmount(),
            $entity->getMeasure(),
            $entity->getIngredient()
                ?->getId(),
            $entity->getRecipe()
                ?->getId(),
            $entity->isAdditional()
        );
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function getMeasure(): string
    {
        return $this->measure;
    }

    public function getIngredient(): ?int
    {
        return $this->ingredient;
    }

    public function getRecipe(): ?int
    {
        return $this->recipe;
    }

    public function isAdditional(): bool
    {
        return $this->additional;
    }
}
