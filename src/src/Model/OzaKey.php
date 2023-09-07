<?php

namespace App\Model;

class OzaKey
{
    private ?string $key;

    public function __construct()
    {
        $this->key = null;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(?string $key): void
    {
        $this->key = $key;
    }
}
