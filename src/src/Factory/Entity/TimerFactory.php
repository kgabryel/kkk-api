<?php

namespace App\Factory\Entity;

use App\Entity\Timer;
use App\Validation\TimerValidation;

class TimerFactory extends EntityFactory
{
    public function create(TimerValidation $timerValidation): ?Timer
    {
        if (!$timerValidation->validate()->passed()) {
            return null;
        }

        $data = $timerValidation->getDto();
        $timer = new Timer();
        $timer->setUser($this->user);
        $timer->setName($data->getName());
        $timer->setTime($data->getTime());
        $this->saveEntity($timer);

        return $timer;
    }
}
