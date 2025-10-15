<?php

namespace App\Dto\Request;

use App\Entity\Ingredient;
use App\Entity\Recipe;

class RecipePosition
{
    private bool $additional;
    private ?float $amount;
    private ?Ingredient $ingredient;
    private string $measure;
    private ?Recipe $recipe;

    public function __construct(
        bool $additional,
        ?float $amount,
        string $measure,
        ?Ingredient $ingredient,
        ?Recipe $recipe,
    ) {
        $this->additional = $additional;
        $this->amount = $amount;
        $this->measure = $measure;
        $this->ingredient = $ingredient;
        $this->recipe = $recipe;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }

    public function getMeasure(): string
    {
        return $this->measure;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function isAdditional(): bool
    {
        return $this->additional;
    }
}
