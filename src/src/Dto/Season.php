<?php

namespace App\Dto;

use App\Entity\Season as Entity;
use InvalidArgumentException;

class Season implements DtoInterface
{
    private int $id;
    private int $ingredientId;
    private int $start;
    private int $stop;

    /**
     * @param  int  $id
     * @param  int  $ingredientId
     * @param  int  $start
     * @param  int  $stop
     */
    public function __construct(int $id, int $ingredientId, int $start, int $stop)
    {
        $this->id = $id;
        $this->ingredientId = $ingredientId;
        $this->start = $start;
        $this->stop = $stop;
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
            $entity->getIngredient()->getId(),
            $entity->getStart(),
            $entity->getStop()
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getStop(): int
    {
        return $this->stop;
    }

    public function getIngredientId(): int
    {
        return $this->ingredientId;
    }
}
