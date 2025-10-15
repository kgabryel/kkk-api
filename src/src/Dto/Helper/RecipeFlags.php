<?php

namespace App\Dto\Helper;

class RecipeFlags
{
    private bool $isFavourite;
    private bool $isPublic;
    private bool $isToDo;

    public function __construct(bool $isFavourite, bool $isToDo, bool $isPublic)
    {
        $this->isFavourite = $isFavourite;
        $this->isToDo = $isToDo;
        $this->isPublic = $isPublic;
    }

    public function isFavourite(): bool
    {
        return $this->isFavourite;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function isToDo(): bool
    {
        return $this->isToDo;
    }
}
