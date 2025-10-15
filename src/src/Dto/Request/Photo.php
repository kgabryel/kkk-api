<?php

namespace App\Dto\Request;

class Photo
{
    private string $decoded;

    public function __construct(string $decoded)
    {
        $this->decoded = $decoded;
    }

    public function getDecoded(): string
    {
        return $this->decoded;
    }
}
