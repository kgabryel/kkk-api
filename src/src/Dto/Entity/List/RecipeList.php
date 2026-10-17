<?php

namespace App\Dto\Entity\List;

use App\Dto\BaseList;
use App\Dto\Entity\Recipe;

/**
 * @extends BaseList<Recipe>
 */
class RecipeList extends BaseList implements DtoList
{
    public function __construct(Recipe ...$recipes)
    {
        $this->entities = $recipes;
    }

    public static function getDtoName(): string
    {
        return Recipe::class;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
