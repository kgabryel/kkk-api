<?php

namespace App\Dto\Request;

class Timer
{
    private ?string $name;
    private int $time;

    public function __construct(?string $name, int $time)
    {
        $this->name = $name;
        $this->time = $time;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getTime(): int
    {
        return $this->time;
    }
}
