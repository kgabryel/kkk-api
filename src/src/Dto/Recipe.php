<?php

namespace App\Dto;

use App\Entity\Photo as PhotoEntity;
use App\Entity\Recipe as Entity;
use App\Entity\RecipePositionGroup;
use App\Entity\Tag as TagEntity;
use App\Entity\Timer as TimerEntity;
use InvalidArgumentException;

class Recipe implements DtoInterface
{
    private int $id;
    private string $name;
    private ?string $description;
    private bool $favourite;
    private bool $toDo;
    private ?string $url;
    private ?int $portions;
    /** @var int[] */
    private array $tags;
    /** @var RecipePositionsGroup[] */
    private array $groups;
    private bool $public;
    private string $publicId;
    private array $timers;
    private array $photos;

    /**
     * Recipe constructor.
     *
     * @param  int  $id
     * @param  string  $name
     * @param  bool  $favourite
     * @param  bool  $toDo
     * @param  string|null  $description
     * @param  string|null  $url
     * @param  int|null  $portions
     * @param  array  $tags
     * @param  RecipePositionGroup[]  $groups
     * @param  bool  $public
     * @param  string  $publicId
     * @param  Timer[]  $timers
     */
    public function __construct(
        int $id,
        string $name,
        bool $favourite,
        bool $toDo,
        ?string $description,
        ?string $url,
        ?int $portions,
        array $tags,
        array $groups,
        bool $public,
        string $publicId,
        array $timers,
        array $photos
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->favourite = $favourite;
        $this->toDo = $toDo;
        $this->description = $description;
        $this->url = $url;
        $this->portions = $portions;
        $this->tags = $tags;
        $this->groups = array_map(
            static fn(RecipePositionGroup $group): RecipePositionsGroup => RecipePositionsGroup::createFromEntity(
                $group
            ),
            $groups
        );
        $this->public = $public;
        $this->publicId = $publicId;
        $this->timers = array_map(
            static fn(TimerEntity $timer): Timer => Timer::createFromEntity($timer),
            $timers
        );
        $this->photos = array_map(
            static fn(PhotoEntity $photo): Photo => Photo::createFromEntity($photo),
            $photos
        );
    }

    /**
     * @param  Entity  $entity
     *
     * @return self
     */
    public static function createFromEntity($entity): self
    {
        if (!($entity instanceof Entity)) {
            throw new InvalidArgumentException(
                printf('Parameter "entity" isn\'t an instance of "%s" class', Entity::class)
            );
        }
        $tags = array_map(
            static fn(TagEntity $tag): int => $tag->getId(),
            $entity->getTags()->toArray()
        );

        return new self(
            $entity->getId(),
            $entity->getName(),
            $entity->isFavourite(),
            $entity->isToDo(),
            $entity->getDescription(),
            $entity->getUrl(),
            $entity->getPortions(),
            $tags,
            array_values($entity->getRecipePositionGroups()->toArray()),
            $entity->isPublic(),
            $entity->getPublicId(),
            array_values($entity->getTimers()->toArray()),
            array_values($entity->getPhotos()->toArray())
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isFavourite(): bool
    {
        return $this->favourite;
    }

    public function isToDo(): bool
    {
        return $this->toDo;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getPortions(): ?int
    {
        return $this->portions;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getTimers(): array
    {
        return $this->timers;
    }

    public function getPhotos(): array
    {
        return $this->photos;
    }

    /**
     * @return RecipePositionsGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
