<?php

namespace App\Dto\Entity;

use JsonSerializable;

class Season implements DtoInterface, JsonSerializable
{
    private int $id;
    private int $ingredientId;
    private int $start;
    private int $stop;

    public function __construct(int $id, int $ingredientId, int $start, int $stop)
    {
        $this->id = $id;
        $this->ingredientId = $ingredientId;
        $this->start = $start;
        $this->stop = $stop;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'ingredientId' => $this->ingredientId,
            'start' => $this->start,
            'stop' => $this->stop,
        ];
    }
}
