<?php

namespace App\Dto\Entity;

use JsonSerializable;

class ApiKey implements DtoInterface, JsonSerializable
{
    private bool $active;
    private int $id;
    private string $key;

    public function __construct(int $id, string $key, bool $active)
    {
        $this->id = $id;
        $this->key = $key;
        $this->active = $active;
    }

    public function jsonSerialize(): array
    {
        return [
            'active' => $this->active,
            'id' => $this->id,
            'key' => $this->key,
        ];
    }
}
