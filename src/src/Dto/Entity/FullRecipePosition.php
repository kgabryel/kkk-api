<?php

namespace App\Dto\Entity;

use JsonSerializable;

class FullRecipePosition implements DtoInterface, JsonSerializable
{
    private bool $additional;
    private ?float $amount;
    private string $ingredient;
    private string $measure;

    public function __construct(
        ?float $amount,
        string $measure,
        bool $additional,
        ?string $ingredient,
        ?string $recipe,
    ) {
        $this->amount = $amount;
        $this->measure = $measure;
        $this->ingredient = $ingredient ?? $recipe ?? '';
        $this->additional = $additional;
    }

    public function jsonSerialize(): array
    {
        return [
            'additional' => $this->additional,
            'amount' => $this->amount,
            'ingredient' => $this->ingredient,
            'measure' => $this->measure,
        ];
    }
}
