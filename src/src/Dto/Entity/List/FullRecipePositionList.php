<?php

namespace App\Dto\Entity\List;

use App\Dto\BaseList;
use App\Dto\Entity\FullRecipePosition;

/**
 * @extends BaseList<FullRecipePosition>
 */
class FullRecipePositionList extends BaseList implements DtoList
{
    public function __construct(FullRecipePosition ...$positions)
    {
        $this->entities = $positions;
    }

    public static function getDtoName(): string
    {
        return FullRecipePosition::class;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
