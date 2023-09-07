<?php

namespace App\Dto;

use App\Entity\ApiKey as Entity;
use InvalidArgumentException;

class ApiKey implements DtoInterface
{
    private int $id;
    private string $key;
    private bool $active;

    public function __construct(int $id, string $key, bool $active)
    {
        $this->id = $id;
        $this->key = $key;
        $this->active = $active;
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

        return new self(
            $entity->getId(),
            $entity->getKey(),
            $entity->isActive()
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
