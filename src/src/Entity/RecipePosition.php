<?php

namespace App\Entity;

use App\Repository\RecipePositionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RecipePositionRepository::class)
 */
class RecipePosition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Ingredient::class, inversedBy="recipePositions")
     */
    private ?Ingredient $ingredient;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private ?float $amount;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $measure;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $additional;

    /**
     * @ORM\ManyToOne(targetEntity=RecipePositionGroup::class, inversedBy="recipePosition")
     * @ORM\JoinColumn(nullable=false)
     */
    private RecipePositionGroup $recipePositionGroup;

    /**
     * @ORM\ManyToOne(targetEntity=Recipe::class, inversedBy="recipePositions")
     */
    private ?Recipe $recipe;

    public function getId(): int
    {
        return $this->id;
    }

    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }

    public function setIngredient(?Ingredient $ingredient): self
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getMeasure(): ?string
    {
        return $this->measure;
    }

    public function setMeasure(string $measure): self
    {
        $this->measure = $measure;

        return $this;
    }

    public function isAdditional(): bool
    {
        return $this->additional;
    }

    public function setAdditional(bool $additional): self
    {
        $this->additional = $additional;

        return $this;
    }

    public function getRecipePositionGroup(): RecipePositionGroup
    {
        return $this->recipePositionGroup;
    }

    public function setRecipePositionGroup(RecipePositionGroup $recipePositionGroup): self
    {
        $this->recipePositionGroup = $recipePositionGroup;

        return $this;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }
}
