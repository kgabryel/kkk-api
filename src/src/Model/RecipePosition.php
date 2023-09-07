<?php

namespace App\Model;

use App\Entity\Ingredient;
use App\Entity\Recipe;

class RecipePosition
{
    private ?float $amount;
    private ?string $measure;
    private ?Ingredient $ingredient;
    private ?Recipe $recipe;
    private ?bool $additional;

    public function __construct()
    {
        $this->amount = null;
        $this->measure = null;
        $this->ingredient = null;
        $this->recipe = null;
        $this->additional = null;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): void
    {
        $this->amount = $amount;
    }

    public function getMeasure(): ?string
    {
        return $this->measure;
    }

    public function setMeasure(?string $measure): void
    {
        $this->measure = $measure;
    }

    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }

    public function setIngredient(?Ingredient $ingredient): void
    {
        $this->ingredient = $ingredient;
    }

    public function isAdditional(): ?bool
    {
        return $this->additional;
    }

    public function setAdditional(?bool $additional): void
    {
        $this->additional = $additional;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): void
    {
        $this->recipe = $recipe;
    }
}
