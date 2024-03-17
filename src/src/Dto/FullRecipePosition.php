<?php

namespace App\Dto;

use App\Entity\RecipePosition as Entity;
use InvalidArgumentException;

class FullRecipePosition implements DtoInterface
{
    private ?float $amount;
    private string $measure;
    private string $ingredient;
    private bool $additional;

    public function __construct(
        ?float $amount,
        string $measure,
        string $ingredient,
        bool $additional
    ) {
        $this->amount = $amount;
        $this->measure = $measure;
        $this->ingredient = $ingredient;
        $this->additional = $additional;
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

        $name = $entity->getRecipe() === null ? $entity->getIngredient()->getName() : $entity->getRecipe()->getName();

        return new self(
            $entity->getAmount(),
            $entity->getMeasure(),
            $name,
            $entity->isAdditional()
        );
    }

    public function getIngredient(): string
    {
        return $this->ingredient;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function getMeasure(): string
    {
        return $this->measure;
    }

    public function isAdditional(): bool
    {
        return $this->additional;
    }
}
