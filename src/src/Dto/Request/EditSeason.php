<?php

namespace App\Dto\Request;

class EditSeason
{
    private int $start;
    private int $stop;

    public function __construct(int $start, int $stop)
    {
        $this->start = $start;
        $this->stop = $stop;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getStop(): int
    {
        return $this->stop;
    }
}
