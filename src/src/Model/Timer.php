<?php

namespace App\Model;

class Timer
{
    private ?string $name;
    private ?int $time;

    public function __construct()
    {
        $this->name = null;
        $this->time = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getTime(): ?int
    {
        return $this->time;
    }

    public function setTime(?int $time): void
    {
        $this->time = $time;
    }
}
