<?php

namespace App\Dto;

use App\Entity\Timer as Entity;
use InvalidArgumentException;

class Timer implements DtoInterface
{
    private int $id;
    private ?string $name;
    private int $time;

    public function __construct(int $id, ?string $name, int $time)
    {
        $this->id = $id;
        $this->name = $name;
        $this->time = $time;
    }

    /**
     * @param  Entity  $entity
     *
     * @return self
     */
    public static function createFromEntity($entity): DtoInterface
    {
        if (!($entity instanceof Entity)) {
            throw new InvalidArgumentException(
                printf('Parameter "entity" isn\'t an instance of "%s" class', Entity::class)
            );
        }

        return new self($entity->getId(), $entity->getName(), $entity->getTime());
    }

    public function getId(): int
    {
        return $this->id;
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
