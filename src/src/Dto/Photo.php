<?php

namespace App\Dto;

use App\Entity\Photo as Entity;
use InvalidArgumentException;

class Photo implements DtoInterface
{
    private int $id;
    private int $width;
    private int $height;
    private string $type;

    public function __construct(int $id, int $width, int $height, string $type)
    {
        $this->id = $id;
        $this->width = $width;
        $this->height = $height;
        $this->type = $type;
    }

    public static function createFromEntity(mixed $entity): self
    {
        if (!($entity instanceof Entity)) {
            throw new InvalidArgumentException(
                sprintf('Parameter "entity" isn\'t an instance of "%s" class', Entity::class)
            );
        }

        return new self(
            $entity->getId(),
            $entity->getWidth(),
            $entity->getHeight(),
            $entity->getType()
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
