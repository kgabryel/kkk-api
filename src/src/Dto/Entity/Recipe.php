<?php

namespace App\Dto\Entity;

use App\Dto\Entity\List\PhotoList;
use App\Dto\Entity\List\RecipePositionsGroupList;
use App\Dto\Entity\List\TimerList;
use App\Dto\Helper\RecipeFlags;
use App\Dto\Helper\RecipeRelatedEntities;
use App\Dto\List\Type\IntList;
use App\Entity\Tag;
use App\Factory\DtoFactoryDispatcher;
use JsonSerializable;

class Recipe implements DtoInterface, JsonSerializable
{
    private ?string $description;
    private bool $favourite;
    private RecipePositionsGroupList $groups;
    private int $id;
    private string $name;
    private PhotoList $photos;
    private int $portions;
    private bool $public;
    private string $publicId;
    private IntList $tags;
    private TimerList $timers;
    private bool $toDo;
    private ?string $url;

    public function __construct(
        int $id,
        string $name,
        ?string $description,
        ?string $url,
        int $portions,
        string $publicId,
        RecipeFlags $recipeFlags,
        RecipeRelatedEntities $relatedEntities,
        DtoFactoryDispatcher $dtoFactory,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->favourite = $recipeFlags->isFavourite();
        $this->toDo = $recipeFlags->isToDo();
        $this->description = $description;
        $this->url = $url;
        $this->portions = $portions;
        $this->tags = new IntList(
            ...array_map(static fn (Tag $tag): int => $tag->getId(), $relatedEntities->getTags()->get()),
        );
        $this->groups = $dtoFactory->getMany(
            RecipePositionsGroupList::class,
            ...$relatedEntities->getGroupsList()->get(),
        );
        $this->public = $recipeFlags->isPublic();
        $this->publicId = $publicId;
        $this->timers = $dtoFactory->getMany(TimerList::class, ...$relatedEntities->getTimersList()->get());
        $this->photos = $dtoFactory->getMany(PhotoList::class, ...$relatedEntities->getPhotosList()->get());
    }

    public function jsonSerialize(): array
    {
        return [
            'description' => $this->description,
            'favourite' => $this->favourite,
            'groups' => $this->groups,
            'id' => $this->id,
            'name' => $this->name,
            'photos' => $this->photos,
            'portions' => $this->portions,
            'public' => $this->public,
            'publicId' => $this->publicId,
            'tags' => $this->tags->get(),
            'timers' => $this->timers,
            'toDo' => $this->toDo,
            'url' => $this->url,
        ];
    }
}
