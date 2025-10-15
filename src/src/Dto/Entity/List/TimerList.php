<?php

namespace App\Dto\Entity\List;

use App\Dto\BaseList;
use App\Dto\Entity\Timer;

/**
 * @extends BaseList<Timer>
 */
class TimerList extends BaseList implements DtoList
{
    public function __construct(Timer ...$timers)
    {
        $this->entities = $timers;
    }

    public static function getDtoName(): string
    {
        return Timer::class;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
