<?php

namespace App\Dto\Request;

class RecipeFlags
{
    public ?bool $favourite;
    public ?bool $toDo;

    public function __construct(?bool $favourite, ?bool $toDo)
    {
        $this->favourite = $favourite;
        $this->toDo = $toDo;
    }

    public function getFavourite(): ?bool
    {
        return $this->favourite;
    }

    public function getToDo(): ?bool
    {
        return $this->toDo;
    }
}
