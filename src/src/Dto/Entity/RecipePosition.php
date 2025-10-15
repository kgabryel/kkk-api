<?php

namespace App\Dto\Entity;

use JsonSerializable;

class RecipePosition implements DtoInterface, JsonSerializable
{
    private bool $additional;
    private ?float $amount;
    private ?int $ingredient;
    private string $measure;
    private ?int $recipe;

    public function __construct(
        ?float $amount,
        string $measure,
        ?int $ingredient,
        ?int $recipe,
        bool $additional,
    ) {
        $this->amount = $amount;
        $this->measure = $measure;
        $this->ingredient = $ingredient;
        $this->recipe = $recipe;
        $this->additional = $additional;
    }

    public function jsonSerialize(): array
    {
        return [
            'additional' => $this->additional,
            'amount' => $this->amount,
            'ingredient' => $this->ingredient,
            'measure' => $this->measure,
            'recipe' => $this->recipe,
        ];
    }
}
