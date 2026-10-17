<?php

namespace App\Dto\Entity;

use App\Dto\Entity\List\FullRecipePositionList;
use JsonSerializable;

class FullRecipePositionsGroup implements DtoInterface, JsonSerializable
{
    protected string $name;
    protected FullRecipePositionList $positions;

    public function __construct(string $name, FullRecipePositionList $positions)
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
