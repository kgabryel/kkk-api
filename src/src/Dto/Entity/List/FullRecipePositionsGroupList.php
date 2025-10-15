<?php

namespace App\Dto\Entity\List;

use App\Dto\BaseList;
use App\Dto\Entity\FullRecipePositionsGroup;

/**
 * @extends BaseList<FullRecipePositionsGroup>
 */
class FullRecipePositionsGroupList extends BaseList implements DtoList
{
    public function __construct(FullRecipePositionsGroup ...$groups)
    {
        $this->entities = $groups;
    }

    public static function getDtoName(): string
    {
        return FullRecipePositionsGroup::class;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
