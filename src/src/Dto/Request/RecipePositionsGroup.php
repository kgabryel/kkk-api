<?php

namespace App\Dto\Request;

use App\Dto\Request\List\RecipePositionList;

class RecipePositionsGroup
{
    private ?string $name;
    private RecipePositionList $positions;

    public function __construct(?string $name, RecipePositionList $positions)
    {
        $this->name = $name;
        $this->positions = $positions;
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }

    public function getPositions(): RecipePositionList
    {
        return $this->positions;
    }
}
