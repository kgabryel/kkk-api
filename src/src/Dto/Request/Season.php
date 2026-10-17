<?php

namespace App\Dto\Request;

use App\Entity\Ingredient;

class Season extends EditSeason
{
    private Ingredient $ingredient;

    public function __construct(Ingredient $ingredient, int $start, int $stop)
    {
        parent::__construct($start, $stop);
        $this->ingredient = $ingredient;
    }

    public function getIngredient(): Ingredient
    {
        return $this->ingredient;
    }
}
