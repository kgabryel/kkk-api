<?php

namespace App\Dto\Entity\List;

use App\Dto\BaseList;
use App\Dto\Entity\Ingredient;

/**
 * @extends BaseList<Ingredient>
 */
class IngredientList extends BaseList implements DtoList
{
    public function __construct(Ingredient ...$ingredients)
    {
        $this->entities = $ingredients;
    }

    public static function getDtoName(): string
    {
        return Ingredient::class;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
