<?php

namespace App\Dto\Entity\List;

use App\Dto\BaseList;
use App\Dto\Entity\RecipePosition;

/**
 * @extends BaseList<RecipePosition>
 */
class RecipePositionList extends BaseList implements DtoList
{
    public function __construct(RecipePosition ...$positions)
    {
        $this->entities = $positions;
    }

    public static function getDtoName(): string
    {
        return RecipePosition::class;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
