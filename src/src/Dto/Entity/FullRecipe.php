<?php

namespace App\Dto\Entity;

use App\Dto\Entity\List\FullRecipePositionsGroupList;
use App\Dto\Entity\List\PhotoList;
use App\Dto\Helper\RecipeRelatedEntities;
use App\Dto\List\Type\StringList;
use App\Entity\Tag as TagEntity;
use App\Factory\DtoFactoryDispatcher;
use JsonSerializable;

class FullRecipe implements DtoInterface, JsonSerializable
{
    private ?string $description;
    private FullRecipePositionsGroupList $groups;
    private int $id;
    private string $name;
    private PhotoList $photos;
    private int $portions;
    private StringList $tags;
    private ?string $url;

    public function __construct(
        int $id,
        string $name,
        ?string $description,
        ?string $url,
        int $portions,
        RecipeRelatedEntities $relatedEntities,
        DtoFactoryDispatcher $dtoFactory,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->url = $url;
        $this->portions = $portions;
        $this->tags = new StringList(
            ...array_map(static fn (TagEntity $tag): string => $tag->getName(), $relatedEntities->getTags()->get()),
        );
        $this->groups = $dtoFactory->getMany(
            FullRecipePositionsGroupList::class,
            ...$relatedEntities->getGroupsList()->get(),
        );
        $this->photos = $dtoFactory->getMany(PhotoList::class, ...$relatedEntities->getPhotosList()->get());
    }

    public function jsonSerialize(): array
    {
        return [
            'description' => $this->description,
            'groups' => $this->groups,
            'id' => $this->id,
            'name' => $this->name,
            'photos' => $this->photos,
            'portions' => $this->portions,
            'tags' => $this->tags->get(),
            'url' => $this->url,
        ];
    }
}
