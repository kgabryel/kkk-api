<?php

namespace App\Entity;

use App\Repository\RecipePositionGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipePositionGroupRepository::class)]
class RecipePositionGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Recipe::class, inversedBy: 'recipePositionGroups')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Recipe $recipe;

    /**
     * @var Collection<int, RecipePosition>
     */
    #[ORM\OneToMany(targetEntity: RecipePosition::class, mappedBy: 'recipePositionGroup')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $recipePosition;

    public function __construct()
    {
        $this->recipePosition = new ArrayCollection();
    }

    public function addRecipePosition(RecipePosition $recipePosition): self
    {
        if (!$this->recipePosition->contains($recipePosition)) {
            $this->recipePosition[] = $recipePosition;
            $recipePosition->setRecipePositionGroup($this);
        }

        return $this;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, RecipePosition>
     */
    public function getRecipePositions(): Collection
    {
        $iterator = $this->recipePosition->getIterator();
        $iterator->uasort(static fn (RecipePosition $a, RecipePosition $b): int => $a->getId() <=> $b->getId());

        return new ArrayCollection(iterator_to_array($iterator, false));
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setRecipe(Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }
}
