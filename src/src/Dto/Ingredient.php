<?php

namespace App\Dto;

use App\Entity\Ingredient as Entity;
use InvalidArgumentException;

class Ingredient implements DtoInterface
{
    private int $id;
    private string $name;
    private bool $available;
    private ?int $ozaId;

    public function __construct(int $id, string $name, bool $available, ?int $ozaId)
    {
        $this->id = $id;
        $this->name = $name;
        $this->available = $available;
        $this->ozaId = $ozaId;
    }

    /**
     * @param  Entity  $entity
     *
     * @return self
     */
    public static function createFromEntity($entity): self
    {
        if (!($entity instanceof Entity)) {
            throw new InvalidArgumentException(
                printf('Parameter "entity" isn\'t an instance of "%s" class', Entity::class)
            );
        }

        return new self(
            $entity->getId(),
            $entity->getName(),
            $entity->isAvailable(),
            $entity->getOzaId()
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

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function getOzaId(): ?int
    {
        return $this->ozaId;
    }
}
