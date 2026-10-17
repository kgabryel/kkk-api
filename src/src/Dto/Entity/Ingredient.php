<?php

namespace App\Dto\Entity;

use JsonSerializable;

class Ingredient implements DtoInterface, JsonSerializable
{
    private bool $available;
    private int $id;
    private string $name;
    private ?int $ozaId;

    public function __construct(int $id, string $name, bool $available, ?int $ozaId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->available = $available;
        $this->ozaId = $ozaId;
    }

    public function jsonSerialize(): array
    {
        return [
            'available' => $this->available,
            'id' => $this->id,
            'name' => $this->name,
            'ozaId' => $this->ozaId,
        ];
    }
}
