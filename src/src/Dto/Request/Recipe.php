<?php

namespace App\Dto\Request;

use App\Dto\List\Type\StringList;
use App\Dto\Request\List\RecipePositionsGroupList;
use App\Dto\Request\List\TimerList;

class Recipe
{
    private ?string $description;
    private bool $favourite;
    private RecipePositionsGroupList $groups;
    private string $name;
    private ?int $portions;
    private bool $public;
    private StringList $tags;
    private TimerList $timers;
    private bool $toDo;
    private ?string $url;

    public function __construct(
        string $name,
        ?string $description,
        ?string $url,
        ?int $portions,
        bool $favourite,
        bool $public,
        bool $toDo,
        StringList $tags,
        TimerList $timers,
        RecipePositionsGroupList $groups,
    ) {
        $this->description = $description;
        $this->favourite = $favourite;
        $this->groups = $groups;
        $this->name = $name;
        $this->portions = $portions;
        $this->public = $public;
        $this->tags = $tags;
        $this->timers = $timers;
        $this->toDo = $toDo;
        $this->url = $url;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getGroups(): RecipePositionsGroupList
    {
        return $this->groups;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPortions(): ?int
    {
        return $this->portions;
    }

    public function getTags(): StringList
    {
        return $this->tags;
    }

    public function getTimers(): TimerList
    {
        return $this->timers;
    }

    public function getUrl(): ?string
    {
        return $this->url;
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
}
