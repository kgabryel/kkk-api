<?php

namespace App\Entity;

use App\Repository\RecipePositionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipePositionRepository::class)]
class RecipePosition
{
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $additional;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $amount;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Ingredient::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Ingredient $ingredient;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $measure;

    #[ORM\ManyToOne(targetEntity: Recipe::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Recipe $recipe;

    #[ORM\ManyToOne(targetEntity: RecipePositionGroup::class, inversedBy: 'recipePosition')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private RecipePositionGroup $recipePositionGroup;

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
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

    public function setAdditional(bool $additional): self
    {
        $this->additional = $additional;

        return $this;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function setIngredient(?Ingredient $ingredient): self
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function setMeasure(string $measure): self
    {
        $this->measure = $measure;

        return $this;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function setRecipePositionGroup(RecipePositionGroup $recipePositionGroup): self
    {
        $this->recipePositionGroup = $recipePositionGroup;

        return $this;
    }
}
