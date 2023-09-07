<?php

namespace App\Model;

class Recipe
{
    private ?string $name;
    private ?string $description;
    private ?string $url;
    private ?bool $favourite;
    private ?bool $toDo;
    private ?int $portions;
    private ?bool $public;
    /** @var Timer[] */
    private array $timers;
    /** @var string[] */
    private array $tags;
    /** @var RecipePositionsGroup[] */
    private array $groups;

    public function __construct()
    {
        $this->name = null;
        $this->description = null;
        $this->url = null;
        $this->favourite = null;
        $this->toDo = null;
        $this->portions = null;
        $this->public = null;
        $this->timers = [];
        $this->tags = [];
        $this->groups = [];
    }

    /**
     * @return RecipePositionsGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param  RecipePositionsGroup[]  $groups
     */
    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    public function getTags(): array
    {
        return array_unique($this->tags);
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getPortions(): ?int
    {
        return $this->portions;
    }

    public function setPortions(?int $portions): void
    {
        $this->portions = $portions;
    }

    public function isFavourite(): ?bool
    {
        return $this->favourite;
    }

    public function setFavourite(?bool $favourite): void
    {
        $this->favourite = $favourite;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(?bool $public): void
    {
        $this->public = $public;
    }

    public function isToDo(): ?bool
    {
        return $this->toDo;
    }

    public function setToDo(?bool $toDo): void
    {
        $this->toDo = $toDo;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getTimers(): array
    {
        return $this->timers;
    }

    public function setTimers(array $timers): void
    {
        $this->timers = $timers;
    }
}
