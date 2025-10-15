<?php

namespace App\Dto\Request\List;

use App\Dto\BaseList;
use App\Dto\Request\RecipePosition;

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
