<?php

namespace App\Dto\Entity\List;

use App\Dto\BaseList;
use App\Dto\Entity\RecipePositionsGroup;

/**
 * @extends BaseList<RecipePositionsGroup>
 */
class RecipePositionsGroupList extends BaseList implements DtoList
{
    public function __construct(RecipePositionsGroup ...$groups)
    {
        $this->entities = $groups;
    }

    public static function getDtoName(): string
    {
        return RecipePositionsGroup::class;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
