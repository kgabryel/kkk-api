<?php

namespace App\Dto\Request\List;

use App\Dto\BaseList;
use App\Dto\Request\Timer;

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
