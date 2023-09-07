<?php

namespace App\Model;

class UpdateSeason
{
    private ?int $start;
    private ?int $stop;

    public function __construct()
    {
        $this->start = null;
        $this->stop = null;
    }

    public function getStart(): ?int
    {
        return $this->start;
    }

    public function setStart(?int $start): void
    {
        $this->start = $start;
    }

    public function getStop(): ?int
    {
        return $this->stop;
    }

    public function setStop(?int $stop): void
    {
        $this->stop = $stop;
    }
}
