<?php

namespace App\Dto\Request;

class EditIngredient
{
    private ?bool $available;
    private ?string $name;
    private ?int $ozaId;

    public function __construct(?string $name, ?bool $available, ?int $ozaId = null)
    {
        $this->name = $name;
        $this->available = $available;
        $this->ozaId = $ozaId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getOzaId(): ?int
    {
        return $this->ozaId;
    }

    public function isAvailable(): ?bool
    {
        return $this->available;
    }
}
