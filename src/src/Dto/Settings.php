<?php

namespace App\Dto;

use App\Entity\Settings as Entity;
use InvalidArgumentException;

class Settings implements DtoInterface
{
    private bool $autocomplete;
    private ?string $ozaKey;
    private string $userType;

    public function __construct(bool $autocomplete, ?string $ozaKey, bool $isStandardUser)
    {
        $this->autocomplete = $autocomplete;
        $this->ozaKey = $ozaKey;
        $this->userType = $isStandardUser ? 'standard' : 'facebook';
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
            $entity->getAutocomplete(),
            $entity->getOzaKey(),
            $entity->getUser()->getFbId() === null
        );
    }

    public function getOzaKey(): ?string
    {
        return $this->ozaKey;
    }

    public function isAutocomplete(): bool
    {
        return $this->autocomplete;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }
}
