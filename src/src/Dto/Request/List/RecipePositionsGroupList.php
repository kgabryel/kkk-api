<?php

namespace App\Dto\Request\List;

use App\Dto\BaseList;
use App\Dto\Request\RecipePositionsGroup;

/**
 * @extends BaseList<RecipePositionsGroup>
 */
class RecipePositionsGroupList extends BaseList
{
    public function __construct(RecipePositionsGroup ...$groups)
    {
        $this->entities = $groups;
    }
}
