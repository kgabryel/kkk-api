<?php

namespace App\Dto\Entity;

use JsonSerializable;

class OzaSupply implements DtoInterface, JsonSerializable
{
    private string $amount;
    private bool $available;
    private int $id;
    private string $name;

    public function __construct(int $id, string $name, bool $available, string $amount)
    {
        $this->id = $id;
        $this->name = $name;
        $this->available = $available;
        $this->amount = $amount;
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount,
            'available' => $this->available,
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
