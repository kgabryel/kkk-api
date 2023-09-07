<?php

namespace App\Entity;

use App\Repository\RecipePositionGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RecipePositionGroupRepository::class)
 */
class RecipePositionGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\ManyToOne(targetEntity=Recipe::class, inversedBy="recipePositionGroups")
     * @ORM\JoinColumn(nullable=false)
     */
    private Recipe $recipe;

    /**
     * @ORM\OneToMany(targetEntity=RecipePosition::class, mappedBy="recipePositionGroup", orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private Collection $recipePosition;

    public function __construct()
    {
        $this->recipePosition = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    /**
     * @return Collection|RecipePosition[]
     */
    public function getRecipePosition(): Collection
    {
        return $this->recipePosition;
    }

    public function addRecipePosition(RecipePosition $recipePosition): self
    {
        if (!$this->recipePosition->contains($recipePosition)) {
            $this->recipePosition[] = $recipePosition;
            $recipePosition->setRecipePositionGroup($this);
        }

        return $this;
    }

    public function removeRecipePosition(RecipePosition $recipePosition): self
    {
        $this->recipePosition->removeElement($recipePosition);

        return $this;
    }
}
