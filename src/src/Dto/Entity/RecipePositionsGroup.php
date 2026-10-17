<?php

namespace App\Dto\Entity;

use App\Dto\Entity\List\RecipePositionList;
use JsonSerializable;

class RecipePositionsGroup implements DtoInterface, JsonSerializable
{
    protected string $name;
    protected RecipePositionList $positions;

    public function __construct(string $name, RecipePositionList $positions)
    {
        $this->name = $name;
        $this->positions = $positions;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'positions' => $this->positions,
        ];
    }
}
