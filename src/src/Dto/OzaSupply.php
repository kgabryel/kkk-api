<?php

namespace App\Dto;

class OzaSupply implements DtoInterface
{
    private int $id;
    private string $name;
    private bool $available;
    private string $amount;

    public function __construct(int $id, string $name, bool $available, string $amount)
    {
        $this->id = $id;
        $this->name = $name;
        $this->available = $available;
        $this->amount = $amount;
    }

    public static function createFromEntity(mixed $entity): DtoInterface
    {
        $amount = sprintf('%s%s', $entity->amount, $entity->unit->shortcut);

        return new self($entity->id, $entity->group->name, $entity->amount > 0, $amount);
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

    public function getAmount(): string
    {
        return $this->amount;
    }
}
