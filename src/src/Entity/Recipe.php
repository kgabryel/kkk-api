<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
class Recipe
{
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $favourite;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    /**
     * @var Collection<int, Photo>
     */
    #[ORM\OneToMany(targetEntity: Photo::class, mappedBy: 'recipe')]
    #[ORM\OrderBy(['photoOrder' => 'ASC'])]
    private Collection $photos;

    #[ORM\Column(type: Types::INTEGER)]
    private int $portions;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $public;

    #[ORM\Column(type: Types::STRING, length: 128)]
    private string $publicId;

    /**
     * @var Collection<int, RecipePositionGroup>
     */
    #[ORM\OneToMany(targetEntity: RecipePositionGroup::class, mappedBy: 'recipe')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $recipePositionGroups;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $tags;

    /**
     * @var Collection<int, Timer>
     */
    #[ORM\OneToMany(targetEntity: Timer::class, mappedBy: 'recipe')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $timers;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $toDo;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $url;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->recipePositionGroups = new ArrayCollection();
        $this->timers = new ArrayCollection();
        $this->photos = new ArrayCollection();
    }

    public function addPhoto(Photo $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
            $photo->setRecipe($this);
        }

        return $this;
    }

    public function addRecipePositionGroup(RecipePositionGroup $recipePositionGroup): self
    {
        if (!$this->recipePositionGroups->contains($recipePositionGroup)) {
            $this->recipePositionGroups[] = $recipePositionGroup;
            $recipePositionGroup->setRecipe($this);
        }

        return $this;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function addTimer(Timer $timer): self
    {
        if (!$this->timers->contains($timer)) {
            $this->timers[] = $timer;
            $timer->setRecipe($this);
        }

        return $this;
    }

    public function clearTags(): self
    {
        $this->tags = new ArrayCollection();

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, Photo>
     */
    public function getPhotos(): Collection
    {
        $iterator = $this->photos->getIterator();
        $iterator->uasort(static fn (Photo $a, Photo $b): int => $a->getPhotoOrder() <=> $b->getPhotoOrder());

        return new ArrayCollection(iterator_to_array($iterator, false));
    }

    public function getPortions(): int
    {
        return $this->portions;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    /**
     * @return Collection<int, RecipePositionGroup>
     */
    public function getRecipePositionGroups(): Collection
    {
        $iterator = $this->recipePositionGroups->getIterator();
        $iterator->uasort(
            static fn (RecipePositionGroup $a, RecipePositionGroup $b): int => $a->getId() <=> $b->getId(),
        );

        return new ArrayCollection(iterator_to_array($iterator, false));
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        $iterator = $this->tags->getIterator();
        $iterator->uasort(static fn (Tag $a, Tag $b): int => $a->getId() <=> $b->getId());

        return new ArrayCollection(iterator_to_array($iterator, false));
    }

    /**
     * @return Collection<int, Timer>
     */
    public function getTimers(): Collection
    {
        $iterator = $this->timers->getIterator();
        $iterator->uasort(static fn (Timer $a, Timer $b): int => $a->getId() <=> $b->getId());

        return new ArrayCollection(iterator_to_array($iterator, false));
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function isFavourite(): bool
    {
        return $this->favourite;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function isToDo(): bool
    {
        return $this->toDo;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setFavourite(bool $favourite): self
    {
        $this->favourite = $favourite;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setPortions(int $portions): self
    {
        $this->portions = $portions;

        return $this;
    }

    public function setPublic(bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function setPublicId(string $publicId): self
    {
        $this->publicId = $publicId;

        return $this;
    }

    public function setToDo(bool $toDo): self
    {
        $this->toDo = $toDo;

        return $this;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
