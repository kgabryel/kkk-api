<?php

namespace App\Dto;

use App\Entity\Photo as PhotoEntity;
use App\Entity\Recipe as Entity;
use App\Entity\RecipePositionGroup;
use App\Entity\Tag as TagEntity;
use InvalidArgumentException;

class FullRecipe implements DtoInterface
{
    private int $id;
    private string $name;
    private ?string $description;
    private ?string $url;
    private ?int $portions;
    /** @var int[] */
    private array $tags;
    /** @var RecipePositionsGroup[] */
    private array $groups;
    private array $photos;

    /**
     * Recipe constructor.
     *
     * @param  string  $name
     * @param  string|null  $description
     * @param  string|null  $url
     * @param  int|null  $portions
     * @param  array  $tags
     * @param  RecipePositionGroup[]  $groups
     */
    public function __construct(
        int $id,
        string $name,
        ?string $description,
        ?string $url,
        ?int $portions,
        array $tags,
        array $groups,
        array $photos
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->url = $url;
        $this->portions = $portions;
        $this->tags = $tags;
        $this->groups = array_map(
            static fn(RecipePositionGroup $group
            ): FullRecipePositionsGroup => FullRecipePositionsGroup::createFromEntity($group),
            $groups
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
        $tags = array_map(static fn(TagEntity $tag): string => $tag->getName(), $entity->getTags()->toArray());

        return new self(
            $entity->getId(),
            $entity->getName(),
            $entity->getDescription(),
            $entity->getUrl(),
            $entity->getPortions(),
            array_values($tags),
            array_values($entity->getRecipePositionGroups()->toArray()),
            array_values($entity->getPhotos()->toArray())
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getId(): int
    {
        return $this->id;
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
