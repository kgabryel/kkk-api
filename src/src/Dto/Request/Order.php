<?php

namespace App\Dto\Request;

class Order
{
    private int $id;
    private int $index;

    public function __construct(int $id, int $index)
    {
        $this->id = $id;
        $this->index = $index;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIndex(): int
    {
        return $this->index;
    }
}
