<?php

namespace App\Dto\Entity;

use JsonSerializable;

class Timer implements DtoInterface, JsonSerializable
{
    private int $id;
    private ?string $name;
    private int $time;

    public function __construct(int $id, ?string $name, int $time)
    {
        $this->id = $id;
        $this->name = $name;
        $this->time = $time;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'time' => $this->time,
        ];
    }
}
