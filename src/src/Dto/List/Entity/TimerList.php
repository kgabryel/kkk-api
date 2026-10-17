<?php

namespace App\Dto\List\Entity;

use App\Dto\BaseList;
use App\Entity\Timer;

/**
 * @extends BaseList<Timer>
 */
class TimerList extends BaseList
{
    public function __construct(Timer ...$timers)
    {
        $this->entities = $timers;
    }
}
