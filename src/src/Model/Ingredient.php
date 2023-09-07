<?php

namespace App\Model;

class Ingredient
{
    private ?string $name;
    private ?bool $available;
    private ?int $ozaId;

    public function __construct()
    {
        $this->name = null;
        $this->available = null;
        $this->ozaId = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function isAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(?bool $available): void
    {
        $this->available = $available;
    }

    public function getOzaId(): ?int
    {
        return $this->ozaId;
    }

    public function setOzaId(?int $ozaId): void
    {
        $this->ozaId = $ozaId;
    }
}
