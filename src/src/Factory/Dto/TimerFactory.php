<?php

namespace App\Factory\Dto;

use App\Dto\Entity\Timer;
use App\Entity\Timer as TimerEntity;
use App\Factory\DtoFactoryDispatcher;
use InvalidArgumentException;

class TimerFactory implements DtoFactoryInterface
{
    public function get(object $entity, DtoFactoryDispatcher $dtoFactoryDispatcher): Timer
    {
        if (!($entity instanceof TimerEntity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', TimerEntity::class),
            );
        }

        return new Timer(
            $entity->getId(),
            $entity->getName(),
            $entity->getTime(),
        );
    }

    public function getDtoName(): string
    {
        return Timer::class;
    }
}
