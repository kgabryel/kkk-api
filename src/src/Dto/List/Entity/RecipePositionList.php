<?php

namespace App\Dto\List\Entity;

use App\Dto\BaseList;
use App\Entity\RecipePosition;

/**
 * @extends BaseList<RecipePosition>
 */
class RecipePositionList extends BaseList
{
    public function __construct(RecipePosition ...$positions)
    {
        $this->entities = $positions;
    }
}
