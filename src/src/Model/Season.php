<?php

namespace App\Model;

use App\Entity\Ingredient;

class Season
{
    private ?Ingredient $ingredient;
    private ?int $start;
    private ?int $stop;

    public function __construct()
    {
        $this->ingredient = null;
        $this->start = null;
        $this->stop = null;
    }

    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }

    public function setIngredient(?Ingredient $ingredient): void
    {
        $this->ingredient = $ingredient;
    }

    public function getStart(): ?int
    {
        return $this->start;
    }

    public function setStart(?int $start): void
    {
        $this->start = $start;
    }

    public function getStop(): ?int
    {
        return $this->stop;
    }

    public function setStop(?int $stop): void
    {
        $this->stop = $stop;
    }
}
