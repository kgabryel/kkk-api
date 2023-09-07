<?php

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IngredientRepository::class)
 */
class Ingredient
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private string $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $available;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="ingredients")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\OneToMany(targetEntity=RecipePosition::class, mappedBy="ingredient", orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private Collection $recipePositions;

    /**
     * @ORM\OneToOne(targetEntity=Season::class, mappedBy="ingredient", orphanRemoval=true)
     */
    private ?Season $season;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $ozaId;

    public function __construct()
    {
        $this->recipePositions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|RecipePosition[]
     */
    public function getRecipePositions(): Collection
    {
        return $this->recipePositions;
    }

    public function addRecipePosition(RecipePosition $recipePosition): self
    {
        if (!$this->recipePositions->contains($recipePosition)) {
            $this->recipePositions[] = $recipePosition;
            $recipePosition->setIngredient($this);
        }

        return $this;
    }

    public function removeRecipePosition(RecipePosition $recipePosition): self
    {
        $this->recipePositions->removeElement($recipePosition);

        return $this;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(Season $season): self
    {
        // set the owning side of the relation if necessary
        if ($season->getIngredient() !== $this) {
            $season->setIngredient($this);
        }

        $this->season = $season;

        return $this;
    }

    public function getOzaId(): ?int
    {
        return $this->ozaId;
    }

    public function setOzaId(?int $ozaId): self
    {
        $this->ozaId = $ozaId;

        return $this;
    }
}
