<?php

namespace App\Dto;

use App\Entity\Tag as Entity;
use InvalidArgumentException;

class Tag implements DtoInterface
{
    private int $id;
    private string $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @param  Entity  $entity
     *
     * @return self
     */
    public static function createFromEntity(mixed $entity): self
    {
        if (!($entity instanceof Entity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', Entity::class)
            );
        }

        return new self(
            $entity->getId(),
            $entity->getName()
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
