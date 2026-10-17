<?php

namespace App\Dto\Entity;

use JsonSerializable;

class Photo implements DtoInterface, JsonSerializable
{
    private int $height;
    private int $id;
    private string $type;
    private int $width;

    public function __construct(int $id, int $width, int $height, string $type)
    {
        $this->id = $id;
        $this->width = $width;
        $this->height = $height;
        $this->type = $type;
    }

    public function jsonSerialize(): array
    {
        return [
            'height' => $this->height,
            'id' => $this->id,
            'type' => $this->type,
            'width' => $this->width,
        ];
    }
}
