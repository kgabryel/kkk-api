<?php

namespace App\Dto\List\Entity;

use App\Dto\BaseList;
use App\Entity\RecipePositionGroup;

/**
 * @extends BaseList<RecipePositionGroup>
 */
class RecipePositionGroupList extends BaseList
{
    public function __construct(RecipePositionGroup ...$groups)
    {
        $this->entities = $groups;
    }
}
