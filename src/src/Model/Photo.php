<?php

namespace App\Model;

class Photo
{
    private ?string $photo;

    public function __construct()
    {
        $this->photo = null;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): void
    {
        $this->photo = $photo;
    }
}
