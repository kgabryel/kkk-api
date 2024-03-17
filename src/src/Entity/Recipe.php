<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RecipeRepository::class)
 */
class Recipe
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;
    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="recipes")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;
    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="recipes")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private Collection $tags;
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $favourite;
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $toDo;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $portions;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $url;
    /**
     * @ORM\OneToMany(targetEntity=RecipePositionGroup::class, mappedBy="recipe", orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private Collection $recipePositionGroups;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $public;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private string $publicId;

    /**
     * @ORM\OneToMany(targetEntity=RecipePosition::class, mappedBy="recipe")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private Collection $recipePositions;

    /**
     * @ORM\OneToMany(targetEntity=Timer::class, mappedBy="recipe")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private Collection $timers;

    /**
     * @ORM\OneToMany(targetEntity=Photo::class, mappedBy="recipe", orphanRemoval=true)
     * @ORM\OrderBy({"photoOrder" = "ASC"})
     */
    private Collection $photos;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->recipePositionGroups = new ArrayCollection();
        $this->recipePositions = new ArrayCollection();
        $this->timers = new ArrayCollection();
        $this->photos = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function isFavourite(): ?bool
    {
        return $this->favourite;
    }

    public function setFavourite(bool $favourite): self
    {
        $this->favourite = $favourite;

        return $this;
    }

    public function isToDo(): bool
    {
        return $this->toDo;
    }

    public function setToDo(bool $toDo): self
    {
        $this->toDo = $toDo;

        return $this;
    }

    public function getPortions(): ?int
    {
        return $this->portions;
    }

    public function setPortions(?int $portions): self
    {
        $this->portions = $portions;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection|RecipePositionGroup[]
     */
    public function getRecipePositionGroups(): Collection
    {
        return $this->recipePositionGroups;
    }

    public function addRecipePositionGroup(RecipePositionGroup $recipePositionGroup): self
    {
        if (!$this->recipePositionGroups->contains($recipePositionGroup)) {
            $this->recipePositionGroups[] = $recipePositionGroup;
            $recipePositionGroup->setRecipe($this);
        }

        return $this;
    }

    public function removeRecipePositionGroup(RecipePositionGroup $recipePositionGroup): self
    {
        $this->recipePositionGroups->removeElement($recipePositionGroup);

        return $this;
    }

    public function clearTags(): self
    {
        $this->tags = new ArrayCollection();

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function setPublicId(string $publicId): self
    {
        $this->publicId = $publicId;

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
            $recipePosition->setRecipe($this);
        }

        return $this;
    }

    public function removeRecipePosition(RecipePosition $recipePosition): self
    {
        $this->recipePositions->removeElement($recipePosition);

        return $this;
    }

    /**
     * @return Collection|Timer[]
     */
    public function getTimers(): Collection
    {
        return $this->timers;
    }

    public function addTimer(Timer $timer): self
    {
        if (!$this->timers->contains($timer)) {
            $this->timers[] = $timer;
            $timer->setRecipe($this);
        }

        return $this;
    }

    public function removeTimer(Timer $timer): self
    {
        if ($this->timers->removeElement($timer) && $timer->getRecipe() === $this) {
            $timer->setRecipe(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Photo>
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
            $photo->setRecipe($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): self
    {
        $this->photos->removeElement($photo);

        return $this;
    }
}
