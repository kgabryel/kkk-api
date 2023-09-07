<?php

namespace App\Model;

class Tag
{
    private ?string $name;

    public function __construct()
    {
        $this->name = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
